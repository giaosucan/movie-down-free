/* this file should be included last */

function $(id)
{
	return document.getElementById(id);
}

Dolphin = (D = new (function(){
	var T = this;
	var req = false;
	
	T.abort = function(){req.abort();E.cancel_draw();I.show_loading(false);};
	
	/* something like JsHttpRequest.query() - AJAX data loader */
	/* it differs from JsHttpRequest.query() - it also shows "loading...", and does not cache anything by default */
	T.qr = function(addr, data, onreadyfunc, nocache, text)
	{
		var undef;
		
		if(typeof(nocache) == typeof(undef)) var nocache = true;
		
		I.show_loading(true, text);
		E.cancel_draw(); /* this is required for many reasons */
		
		var beg = (new Date()).getTime();
		
		var r = new JsHttpRequest();
		
		req = r;
		
		r.onerror = function(msg)
		{
			I.show_loading(false,text);
			
			if(msg.length > 100) msg = msg.substr(0, 100) + '...';
			
			if(r.status)
			{
				switch(r.status)
				{
				case 500:
					msg = 'Internal server error';
					break;
				case 503:
				case 502:
					msg = 'The server is temporarily busy';
					break;
				case 404:
					alert('AJAX request failed because of 404 error (Not Found). Please ensure, that SNAME is installed properly.');
					return false;
				case 403:
					alert('AJAX request failed because of 403 error (Permission denied). Please ensure, that you have set correct rights to PHP files.');
					return false;
				}
			}
			
			if(confirm('AJAX subrequest failed.\nThe technical reason: ' + msg + '\n\nDo you want to send that request again?')) T.qr(addr, data, onreadyfunc, nocache);
		}
		
		r.onreadystatechange = function()
		{
			if(r.readyState==4)
			{
				var time = Math.round(((new Date()).getTime() - beg)*1000)/1000000;
				
				I.show_loading(false,text);
				
				if(r.responseText != '--error-login-required')
				{
					try{
						onreadyfunc(r.responseJS, r.responseText);
					}catch(e){}
				}else
				{
					if(confirm('Session has expired, relogin required.\nDo you want to relogin now?'))
						T.qr('index.php', {login: prompt('login:'), pass: prompt('password:'), 'DIR': Engine.address}, function(res, err){
							T.qr(addr, data, onreadyfunc, nocache);
						});
				}
				
				var total = Math.round(((new Date()).getTime() - beg)*1000)/1000000;
				//I.dbg('http+php: '+time+' sec, http+php+js+html: '+total+' sec');
			}
		}
		
		r.caching = !nocache;
		
		r.open(null,addr,true);
		
		r.send(data);
	}
	
	T.init = function()
	{
		I.generate_panel();
		T.resize();
		
		if(interv)
		{
			clearInterval(interv);
			interv = null;
		}
		
		$('loading').style.visibility='hidden';
		$('very_main').style.visibility='visible';
		
		I.change_address();
		
		// init Drag'n'Drop Interface
		
		/*__DDI__.setPlugin('dragStateAsCursor');
	    __DDI__.setPlugin('draggedElementTip');
	    __DDI__.setPlugin('fixNoMouseSelect');
	    __DDI__.setPlugin('lockCursorAsDefault');
	    __DDI__.setPlugin('fixDragInMz');
	    __DDI__.setPlugin('resizeIT');
	    __DDI__.setPlugin('moveIT');*/
	}
	
	T.resize = function()
	{
		I.resize();
	}
	
	var _pingpong_failed = false;
	
	T.pingpong = function()
	{	
		T.qr('index.php?act=ping', 'ping', function(res,err)
		{
			/* prevent from multiple alerts */
			if(res!='pong' && !_pingpong_failed)
			{
				alert('PING-PONG request to server failed. Please check your internet connection.');
				_pingpong_failed = true;
			}else if(res=='pong')
			{
				_pingpong_failed = false;
			}
		});
	}
})());