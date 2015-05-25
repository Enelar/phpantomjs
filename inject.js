var system = require('system');

// Correct file execute is 'file' 'in_file' 'out_file'
if (system.args.length < 3)
  phantom.exit();

/***
 * Reading arguments object from file
 ***/
function GetInitObject()
{
  var in_file = system.args[1];
  var out_file = system.args[2];

  var fs = require('fs');
  var in_string = fs.read(in_file);
  var in_object = JSON.parse(in_string);

  in_object.phantomjs_output_tunnel_file = out_file;
  return in_object;
}

var args = GetInitObject();
var result = {};

/***
 * Preparing to download page
 ***/

var page = require('webpage').create();
page.settings.loadImages = false;
page.settings.localToRemoteUrlAccessEnabled = true;
page.settings.resourceTimeout = 3000; // 15 seconds
page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36';
page.onResourceTimeout = function(e) {
  console.log('timeout');
  phantom.exit(1);
};

phantom.addCookie({
  'name'     : 'yandex_gid',   /* required property */
  'value'    : 2,  /* required property */
  'domain'   : 'market.yandex.ru',
  'path'     : '/',                /* required property */
  'expires'  : (new Date()).getTime() + (1000 * 60 * 60)   /* <-- expires in 1 hour */
});

page.onResourceError = function(resourceError) {
    page.reason = resourceError.errorString;
    page.reason_url = resourceError.url;
};

/***
 * Download page and execute code
 ***/

page.open(args.url, function (status) 
{
  if (status !== 'success')
  {
    console.log('LOAD_FAIL');
    result.error = {};
    result.error.reason = page.reason;
    result.error.reason_url = page.reason_url;
    result.error.status = status;
    result.page = JSON.stringify(page);

    exit_now();
  }
  else 
  {     
    var code = args.inject_code;
    page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js", 
      function() 
      {
        var res = page.evaluate(function(code)
          {
            var ret = {};
            eval(code);
            return ret;
          }, code);
        tunnel_result_and_exit(res)
      });
  }
});  


/***
 * Preparing to exit
 ***/

var export_flag = false;

// Wait for result
(function wait_for_flag()
{
  if (export_flag)
    return exportf(result);
  setTimeout(arguments.callee, 100);  
})();

function tunnel_result_and_exit(res)
{
  result = res;
  exit_now();
}

function exit_now()
{
  export_flag = true;  
}

var exportf = function(ret)
{
  var fs = require('fs');

  result.sys = {page: page.content};
  fs.write(args.phantomjs_output_tunnel_file, JSON.stringify(result), 'w');  
  phantom.exit();
};