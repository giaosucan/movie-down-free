Interface = (I = new (function(){
	
	var T = this;
	
	T.coords = { // 'name': {right, top, width, height}
	/** overall.FVER.png **/
	back: [0, 0, 82, 30],
	back_disabled: [0, 582, 82, 30],
	fwd: [0, 90, 47, 30],
	fwd_disabled: [0, 612, 47, 30],
	up: [48, 90, 29, 30],
	up_disabled: [71, 612, 29, 30],
	search: [0, 180, 66, 30],
	dirs: [0, 270, 65, 30],
	view: [0, 360, 37, 30],
	
	close: [72, 360, 28, 30],
	go: [0, 450, 85, 22],
	
	// left menu
	
	l_uarr: [77, 90, 23, 23],
	l_darr: [77, 136, 23, 23],
	
	// table view
	
	tv_sep: [64, 360, 8, 20],
	tv_lsep: [94, 450, 6, 20],
	tv_uarr: [68, 439, 9, 5],
	tv_darr: [63, 445, 9, 5],
	
	/** menu_all.png **/
	m_open: [0,0,16,16],
	m_mkdir: [0,16,16,16],
	m_upload: [0,32,16,16],
	m_rename: [0,48,16,16],
	m_cut: [0,64,16,16],
	m_copy: [0,80,16,16],
	m_delete: [0,96,16,16],
	m_control_panel: [0,112,16,16],
	m_admin: [0,128,16,16],
	m_paste: [0,160,16,16],
	m_cancel: [0,176,16,16],
	m_zip: [0, 192, 16, 16]
	};
	
	T.dbg = function(message)
	{
		var el = $('debug');
		if(!el)
		{
			el = document.createElement('div');
			el.id = 'debug';
			document.body.appendChild(el);
		}
		el.innerHTML = message;
	}
	
	T.get_width = function()
	{
		if(document.body.offsetWidth) return document.body.offsetWidth;
	  	else if(window.innerWidth) return window.innerWidth;
	  	else return false;
	}
	
	T.get_height = function()
	{
		if(document.body.offsetHeight) return document.body.offsetHeight;
	  	else if(window.innerHeight) return window.innerHeight;
	  	else return false;
	}
	
	// the function (taken from xpoint.ru), which determines the coordinates of an object
	
	T.get_bounds = function(element)
	{
		var left = element.offsetLeft;
		var top = element.offsetTop;
		for (var parent = element.offsetParent; parent; parent = parent.offsetParent)
		{
			left += parent.offsetLeft;
			top += parent.offsetTop;
		}
		return {left: left, top: top, width: element.offsetWidth, height: element.offsetHeight};
	}
	
	T.menu_hover = function()
	{
	}
	
	T.menu_out = function()
	{
	}
	
	// the function changes the current path (changes title, adress and icon)
	
	T.change_path = function(address,dir,type)
	{
		var a = $('address_img');
		var h = $('header_icon');
		
		if(type==0 /* tDIR */)
		{
			a.style.backgroundPosition = '-0px -516px';
			h.style.backgroundPosition = '-37px -360px';
		}else if(type==2 /* tDRIVE */)
		{
			a.style.backgroundPosition = '-0px -538px';
			h.style.backgroundPosition = '-37px -390px';
		}else if(type==3 /* tMYCOMP */)
		{
			a.style.backgroundPosition = '-0px -560px';
			h.style.backgroundPosition = '-37px -420px';
		}
		
		$('name_of_folder').innerHTML = dir;
		
		$('address').value = address;
	}
	
	// the function changes the current path (calls go2)
	// if path is specified, it must be relative
	
	T.change_address = function(path)
	{
		var p = $('address').value;
		
		if(path) p = E.address+'/'+path;
		
		if(p==E.address) E.refresh();
		else E.go2(p);
	}
	
	// function changes the status string, params = [ ..., [ name, value ], ... ]
	
	T.change_status = function(pr)
	{
		var el = $('status_str');
		var tmp = [];
		var j = 0;
		
		for(var k in pr)
		{
			var p = pr[k];
			if(!p[1]) continue;
			
			tmp[j++] = p[0]+': '+p[1];
		}
		
		el.innerHTML = tmp.join('&nbsp;<img src="f/i/no.png" width=8 height=27 border=0 style="padding: 0px; margin: 0px; background: url(\'f/i/overall.FVER.png\'); background-position: -85px -516px;" align="absmiddle">&nbsp;');
	}
	
	// function that sets another image (name - the name of image, state - '', 'h' or 'd')
	// iname - the real image name, if not specified, by default takes name from id ( btn_NAME )
	
	T.im = function(obj,state,iname)
	{
		if(!iname) var iname = obj.id.substr(4);
		var c = T.coords[iname];
		var offset = 0;
		
		if(state=='h') offset = -c[3];
		if(state=='d') offset = -c[3]*2;
		
		obj.style.backgroundPosition = '-' + c[0] + 'px -' + (c[1] - (obj.id.substr(obj.id.length-9,9)=='_disabled' ? 0 : offset)) + 'px';
	}
	
	// this function returns the centered message (as in Windows)
	
	var _msg = function(msg,nowidth)
	{
		return '<table'+(nowidth ? '' : ' width="100%"')+' height="100%"><tr><td style="vertical-align: middle; text-align: center;">'+msg+'</td></tr></table>';
	}
	
	// function that generates panel images, and generates upper panel
	
	T.generate_panel = function()
	{
		var el = $('panel');
		var tmp = '';
		var coords = T.coords;
		
		var act = {
			back: "E.go_back();",
			back_disabled: "return false;", // the disabled elements have style="display: none"
			fwd: "E.go_fwd();",
			fwd_disabled: "return false;",
			up: "I.change_address('..');",
			up_disabled: "return false;"/*,
			search: "I.dbg('search');",
			dirs: "I.dbg('dirs');",
			view: "I.dbg('view');"*/
		};
		
		var lang = {
			back: 'Back',
			back_disabled: 'Back',
			fwd: 'Forward',
			fwd_disabled: 'Forward',
			up: 'Up',
			up_disabled: 'Up',
			search: 'Search',
			dirs: 'Folders',
			view: 'View'
		};
		
		for(var k in act)
		{
			tmp += '<img id="btn_' + k + '" src="f/i/no.png" onmouseover="I.im(this,\'h\');" onmouseout="I.im(this,\'\');" onmousedown="I.im(this,\'d\');" onmouseup="I.im(this,\'h\'); ' + act[k] + ' " alt="' + lang[k] + '" title="' + lang[k] + '" style="background: url(\'f/i/overall.FVER.png\'); background-position: -' + coords[k][0] + 'px -' + coords[k][1] + 'px;' + (k.substr(k.length-9,9)=='_disabled' ? 'display: none;' : '') + '" width="' + coords[k][2] + '" height="' + coords[k][3] + '" />';
		}
		
		el.innerHTML = tmp;
		
		var el = $('upperpanel');
		
		var upper_panel = {
			Update: 'Update&nbsp;to&nbsp;latest&nbsp;development&nbsp;version'/*,
			File: 'File',
			Edit: 'Edit',
			View: 'View',
			Tools: 'Tools',
			Help: 'Help'*/
		};
		
		tmp = '<table width="100%" cellspacing=0 cellpadding=0 border=0><tr height=2><td colspan=6></td></tr><tr height=18 class="menu">';
		
		for(var k in upper_panel)
		{
			//tmp += '<div class="menuelm" onmousedown="I.upperpanel(\'' + k + '\',event,this);" onmouseover="this.className=\'menuelm_hover\';" onmouseout="this.className=\'menuelm\';">' + _msg(upper_panel[k],true) + '</div>';
			tmp += '<td height=18 valign=middle onmouseover="this.className=\'menuelm_hover\'" onmouseout="this.className=\'\';" onmousedown="I.upperpanel(\'' + k + '\',event,this);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+upper_panel[k]+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
		}
		
		el.innerHTML = tmp + '<td width="100%">&nbsp;</td></tr><tr height=4><td colspan=6></td></tr></table>';
	}
	
	// function used to show the Upper Panel Menu
	
	T.upperpanel = function(name,e,obj)
	{
		var x=10,y=10;
		
		/*if(obj.x)
		{
			x=obj.x;
			y=obj.y;
		}else if(e.offsetX)
		{
			x = event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft - event.offsetX;
			y = event.clientY + document.documentElement.scrollTop + document.body.scrollTop - event.offsetY;
		}
		
		y += obj.clientHeight - 3;*/
		
		if(name!='Update')
		{
			var bounds = T.get_bounds(obj);
			
			var el = $('debug');
			if(!el) return;
			
			el.style.position = 'absolute';
			el.style.top = (parseInt(bounds['top']) + parseInt(bounds['height'])) + 'px';
			el.style.left = bounds['left'] + 'px';
			el.innerHTML = 'menu';
		}else
		{
			if(!confirm('Check for newer version?')) return;
			
			D.qr('MASTER_SITE'+'build-info/', {}, function(res,err)
			{
				if(!res) alert('Could not contact MASTER_SITE.');
				else if(res == FVER) alert('No new version available');
				else if(res < FVER) alert('You have a newer version, than on a server :).');
				else if(confirm('New version ('+res+' build) is available.\nInstall it?'))
				{
					E.run_update();
				}
			});
		}
	}
	
	T.resize = function()
	{
		/* T.dbg(T.get_height()); */
		
		var ids = {files: 'main', left_menu_div: 'left'};
		var voff = 221, min = 200; /* vertical offset */
		
		if(!T.initval)
		{
			T.initval = {
				
				'main': Math.max(T.get_height() - voff, min),
				'left': Math.max(T.get_height() - voff, min),
				'h':    T.get_height()
				
			};
			
			for(var k in ids)
			{
				$(k).style.height = T.initval[ids[k]] + 'px';
			}
		}else
		{
			var off = (T.initval.h - T.get_height());
			$('files').style.height = ( $('main').style.height = Math.max(T.initval.main - off,min) + 'px' );
			$('left_menu_div').style.height = ( $('left').style.height = Math.max(T.initval.left - off, min) + 'px' );
		}
	}
	
	T.disable_buttons = function()
	{
		for(var k in {fwd:'',back:'',up:''})
		{
			if(!E['can_go_'+k]())
			{
				$('btn_'+k).style.display='none';
				$('btn_'+k+'_disabled').style.display='';
			}else
			{
				$('btn_'+k).style.display='';
				$('btn_'+k+'_disabled').style.display='none';
			}
		}
	}
	
	// all functions with "upload" work with the upload form in the left menu
	
	T.show_upload = function()
	{
		var el = $('upload_form');
		
		if(el.style.display == 'none')
		{
			el.style.display = '';
			T._append_upload();
		}
		else
		{
			T._clear_uploads();
			el.style.display = 'none';
		}
	}
	
	var _created = []; // array of all appended and created elements to the form
	
	T._append_upload = function()
	{
		if(!T.i) T.i=0;
		
		var el = $('uploads_container');
		
		var obj = document.createElement('input');
		obj.type = 'file';
		obj.className = 'upl';
		obj.name = 'files['+(T.i++)+']';
		
		el.appendChild(obj);
		_created.push(obj);
		obj = document.createElement('br');
		el.appendChild(obj);
		_created.push(obj);
	}
	
	T._clear_uploads = function()
	{
		var el = $('uploads_container');
		
		for(var i = 0; i<_created.length; i++)
		{
			/*try{*/el.removeChild(_created[i]);/*}catch(e){}*/
			//alert(_created[i]);
		}
		_created = [];
	}
	
	// function that shows that something is loading (if state is true - loading started, false - loading finished)
	
	T.show_loading = function(state, text)
	{
		var d = $('loading');
		var s = text||'loading...';
		
		if(state==true && s.indexOf('...')!=-1 && !$('loading_dots'))
		{
			s = s.replace('...', '<span id="loading_dots">...</span>');
			interv = setInterval(function(){var d=$('loading_dots');if(!d)return;if(!this.cnt)this.cnt=2;if(cnt==1)d.innerHTML='...';if(cnt==2)d.innerHTML='&nbsp;..';if(cnt==3)d.innerHTML='.&nbsp;.';if(cnt==4)d.innerHTML='..&nbsp;';cnt++;if(cnt>4)cnt=1;},600);
		}else if(!state && interv)
		{
			clearInterval(interv);
			interv = null;
		}
		
		if(state) s+=' <a href="#" onclick="D.abort();return false;" style="color: green;"><b><u>abort</u></b></a>';
		
		if(state==true)
		{
			d.innerHTML = s;
			d.style.visibility = 'visible';
		}else
		{
			d.style.visibility = 'hidden';
			d.innerHTML = s;
		}
	}
	
	T.window_open = function(src, name, width, height)
	{	
		return window.open(src, name, 'width=' + width + ',height= ' + height + ',resizeable=0,menubar=0,location=0,scrollbars=1,toolbar=0,status=0,top='+(screen.height/2-height/2)+',left='+(screen.width/2-width/2));
	}
	
	T.handle_keydown = function(e)
	{
		// T.dbg('handled');
		
		var sel = R.get_selected_items();
		var filt = E.get_filtered_items();
		var items = E.get_filtered_items();
		var t = e.srcElement || e.target;
		
		if(R.is_smpl_view() || filt.length == 0) return true;
		
		if(R.is_inp(e))
		{
			if(e.keyCode!=38 && e.keyCode!=40) return true;
			else t.blur(e);
		}
		
		
		// T.dbg(e.keyCode + ' ' + e.charCode + ' ' + Math.random());
		
		switch(e.keyCode || e.charCode)
		{
		case 46 /*delete*/:
			if(sel.length>=1)
			{
				E['delete_item'+(sel.length>1?'s':'')]();
				return false;
			}else
			{
				return true;
			}
			break;
		case 113 /*F2*/:
			if(sel.length==1)
			{
				E.rename_item();
				return false;
			}else
			{
				return true;
			}
			break;
		case 38 /* KEYUP */:
		case 40 /* KEYDOWN */:
			if(filt.length!=E.get_global_items().length) return false; /* implementation of arrows is buggy when filter is active */
		
			var id = sel[sel.length-1] ? sel[sel.length-1].id : (filt[0]&&filt[0].k?filt[0].k:0);
			var mstep = e.keyCode ==38 ? 1 : -1;
			var el = $('it'+id);
			var old_id = +id;
			
			if(mstep == 1)
			{
				if(id>0 && !$('it'+(id-1)))
				{
					var prev = id;
					for(var k in filt)
					{
						if(filt[k]['k'] == id) break;
						prev = filt[k]['k'];
					}
					
					id = prev;
				}else if(id>0)
				{
					id-=mstep;
				}
			}else
			{
				if(id<items.length-1 && !$('it'+(id-(-1))))
				{
					var brknext = false, t = null;
					
					for(var k in filt)
					{
						if(brknext)
						{
							id = filt[k]['k'];
							break;
						}
						if(filt[k]['k'] == id) brknext = true;
					}
				}else if(id<E.get_global_items().length-1)
				{
					id-=mstep;
				}
			}
			
			if(id==old_id) return;
			if(R.is_selected(items[id]) || sel.length==2 && sel[0]==items[id])
			{
				id = old_id;
			}
			
			/*I.dbg(id);*/
			$('it'+id).onmousedown(e);
			
			return false;
			break;
		case 13 /* enter */:
			var el;
			if(sel.length==1 && (el=$('it'+sel[0]['id'])))
			{
				el.ondblclick(e);
			}
			return false;
			break;
		case 8 /* backspace */:
			T.change_address('..');
			return false;
			break;
		case 67 /* C (and Ctrl) */:
		case 99 /* charCode */:
			if(!e.ctrlKey || sel.length==0) break;
			E.copy_item();
			break;
		case 88 /* X (and Ctrl) */:
		case 120 /* charCode */:
			if(!e.ctrlKey || sel.length==0) break;
			E.cut_item();
			break;
		case 86 /* V (and Ctrl) */:
		case 118 /* charCode */:
			if(!e.ctrlKey) break;
			E.paste_items();
			break;
		}
		
		return true;
	}
})());