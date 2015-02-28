var Render_Views = 
{
table: function(){
	var T = this;
	T._selected = []; // of course, should be private...
	var FILE_OVER = false;
	var _filter = '';
	
	T.get_selected_items = function()
	{
		return T._selected;
	}
	
	// this function returns the centered message (as in Windows)
	
	var _msg = function(msg)
	{
		return '<table width="100%" height="100%"><tr><td style="vertical-align: middle; text-align: center;">'+msg+'</td></tr></table>';
	}
	
	// function, that is used in Table View to highlight the view
	
	T._tv_h = function(obj,ev)
	{
		
		switch(ev)
		{
		case 'over':
			I.im(obj.firstChild,'h','tv_lsep');
			obj.className = 'h';
			break;
		case 'out':
			I.im(obj.firstChild,'','tv_lsep');
			obj.className = '';
			break;
		case 'down':
			I.im(obj.firstChild,'d','tv_lsep');
			obj.className = 'd';
			break;
		}
	}
	
	T.is_tag = function(e,tagname)
	{
		if(!e || (e.target || e.srcElement).nodeName.toLowerCase()!=tagname.toLowerCase()) return false;
		return true;
	};
	
	T.is_inp = function(e)
	{
		return T.is_tag(e,'input');
	};
	
	
	var _simple_view = false;
	
	T.is_smpl_view = function() { return _simple_view; }
	
	T.draw = function(items,force)
	{
		var begin, end;
		
		var tmp=[];
		
		var i = false;
		
		var c = I.coords;
		
		var w /* width */ = { 'name': $('files').clientWidth - 170, 'size': 50, 'modified': 100 };
		
		/*var cols = '<th id="tv_name" width="' + w['name'] + '" onmouseover="R._tv_h(this,\'over\');" onmouseout="R._tv_h(this,\'out\')" onmousedown="R._tv_h(this,\'down\');"><img src="f/i/no.png" width="'+ c['tv_lsep'][2] +'" height="'+ c['tv_lsep'][3] +'" style="background: url(\'f/i/overall.78.png\'); background-position: -'+c['tv_lsep'][0]+'px -'+c['tv_lsep'][1]+'px;" border=0 align="absmiddle">&nbsp;Name</th>';*/
		
		T.un_cl();
		E.set_filtered_items(items);
		
		begin = '<table width="100%" cellspacing=0 cellpadding=0 border=0>'+/*<thead><tr class="upper" height="20">' + cols + '<th class="bg"><img src="f/i/no.png" width="'+ c['tv_sep'][2] +'" height="'+ c['tv_sep'][3] +'" style="background: url(\'f/i/overall.78.png\'); background-position: -'+c['tv_sep'][0]+'px -'+c['tv_sep'][1]+'px; position: relative; left: -10px;" border=0 align="absmiddle">&nbsp;</th></thead></tr>*/'<tbody>';
		
		for(var k in items)
		{
			var i = items[k];
			var _k = (i.k ? i.k : k); // the global number (in _items), required for the filter usage
			
			var dbl = (i['type']==0 || i['type']==2 ? 'E.go2(E.path(' + _k + '));' : 'E.edit_file_for_item(' + _k + ');' );
			
			var click = 'R.cl(this,event);';
			
			tmp[k] = '<tr><td style="overflow: hidden;"><div class="d16"><span id="it' + _k + '" class="item16" onmousedown="if(!R.is_inp(event)){' + click + ';return false;}" ondblclick="if(!R.is_inp(event)) {' + dbl + '; return false;};" onmouseover="R.handle_over(event,this);" onmouseout="R.handle_out(event,this);"><img src="f/iconz/16-' + i['icon'] + '.png" width=16 height=16 border=0 class="i16" align="absmiddle">' + i['name'] + '</span></div></td><td>&nbsp;</td></tr>';
		}
		
		end = '</tbody></table>';
		
		if(tmp.length==0)
		{
			begin=(end='');
			tmp = [_msg((_filter.length>0 ? 'Result of filtering is empty.' : 'Directory is empty'))];
		}
		if(tmp.length>200 && !force)
		{
			_simple_view = true;
			
			begin+='<tr><td><div align="center" style="color: red; padding: 10px;"><b>it make take a while to display all files in normal mode.<br><a href="#" onclick="R.draw(E.get_filtered_items(),true);return false;">click here to render in normal mode anyway</a></b></div><pre style="padding: 10px; padding-top: 0px;">';
			for(var k in items)
			{
				var i = items[k];
				
				if(i['type']==0 || i['type']==2) tmp[k] = '<a href="#" onclick="E.go2(E.path(' + (i.k ? i.k : k) + '));">' + i['name'] + '</a>/\n';
				else tmp[k] = i['name'] + '\n';
			}
			end = '</div></td></tr>' + end;
			/*
			tmp = [_msg('The result contains more than 1000 files and folders, please enter the filter.')];
			*/
			
		}else
		{
			_simple_view = false;
		}
		
		$('files').innerHTML = begin + tmp.join('') + end;
		
		/*
	    *  allow drag for heading
	    */
	   /* function handleHeadDragStart (e) {
	      e.__dataTransfer.setData('domnode',e.__currentTarget);
	      e.__dataTransfer.effectAllowed = 'move';
	      __DDI__.disablePlugin('dragStateAsCursor');
	      return true;
	    }
	    function handleHeadDragEnd (e) {
	      __DDI__.enablePlugin('dragStateAsCursor');
	    }
	    /*
	    *  allow resize for heading
	    */
	   // function allowHeadResizeHoriz (e) {
	      /*
	      *  flag here that it's only drag
	      */
//	      return {'x':true,'y':false}
//	    }
//	    function handleHeadResizeStart (e) {
//	      return true;
//	    }
//	    function handleHeadResize (e) {
//	      var ct = e.__currentTarget;
//	      var tbody = ct.parentNode.parentNode.parentNode.getElementsByTagName('TBODY')[0];
//	      var tr = tbody.rows;
//	      var trL = tr.length;
//	      for (var i=0; i<trL; i++) {
//	        tr[i].cells[ct.cellIndex].style.width = ct.style.width
//	      }
//	      return {'x' : { 'resize': true
//	                    },
//	              'y' : { 'resize' : false}
//	             }
//	    }*/
		
		/*var thead = $('files').getElementsByTagName('thead')[0];
	    var th = thead.getElementsByTagName('th');
	    var thL = th.length;
	    for (var i = 0; i<thL; i++) {*/
		//{
			//var t = $('tv_name');
	      //if(th[i].innerHTML=='&nbsp;') continue;
	     /* t.__onResizeBefore = allowHeadResizeHoriz;
	      t.__onResize = handleHeadResize;
	      t.__onResizeStart = handleHeadResizeStart;
	      t.__onDragStart = function (e) {
	          if (e.__target.style.cursor != '')
	            return true;
	          else
	            return false;
	        }*/
	    //}
	};
	
	// the function filters the items by name
	
	T.filter = function(s)
	{
		var i = E.get_global_items();
		var n = []; // new items
		var l = s.length;
		var j = -1;
		_filter = (s = s.toLowerCase());
		
		if(l>0)
		{
			for(var k in i) if(i[k]['name'] && i[k]['name'].toLowerCase().indexOf(s)!=-1)
			{
				n[++j] = i[k];
				n[j]['k'] = k;
			}
			T.draw(n);
		}else
		{
			T.draw(i);
		}
	}
	
	/* returns index of element in _selected or boolean false otherwise
	   NOTE: check for true as !==false (not ==true) */
	
	T.is_selected = function(el)
	{
		for(var i=0; i<T._selected.length; i++)
		{
			if(T._selected[i] == el) return i;
		}
		
		return false;
	}
	
	// this function selects the file or folder - the element el
	
	T.cl = function(el,e)
	{
		if(_simple_view) return false;
		
		if(!e.ctrlKey) T.un_cl();
		
		var id = el.id.substr(2);
		
		var i = E.get_global_items()[id];
		
		i['id'] = id;
		
		var num = T.is_selected(i);
		
		if(e.ctrlKey && num!==false)
		{
			T._selected.splice(num,1);
			el.className = 'item16';
		}
		else
		{
			T._selected.push(i);
			el.className = 'item16_h';
		}
		
		if(T._selected.length == 0) T.un_cl(true);
		else if(T._selected.length == 1)
		{
			E.draw_menu_for_item(id);
		}else
		{
			E.draw_menu_for_items();
		}
	}
	
	// this function cancels selection. If force = true, it will also draw the menu
	
	T.un_cl = function(force)
	{
		if(_simple_view) return false;
		
		if(force)
		{
			L.draw(E.get_global_menu());
			I.change_status(E.get_global_status());
		}
		
		for(var k=0; k < T._selected.length; k++)
		{
			$('it' + T._selected[k]['id']).className = 'item16';
		}
		
		T._selected = [];
		
		//last_items_global = {};
	}
	
	// this function handles the onmouseover event for table view. It would use the common for all interface
	
	T.handle_over = function(e, obj)
	{
		if(_simple_view) return false;
		FILE_OVER = true;
	}
	
	// this function handles the onmouseout event for table view. It would use the common for all interface
	
	T.handle_out = function(e, obj)
	{
		if(_simple_view) return false;
		FILE_OVER = false;
	}
	
	// 
	
	T.handle_down = function(e)
	{
		if(_simple_view) return false;
		if(T.is_inp(e) || T.is_tag(e,'a')) return true;
		
		if(!FILE_OVER) T.un_cl(true);
		
		return true/*false*/ /* for input.onblur() worked correctly. Need to make changes in event system to handle this case better. */;
	}
}
};

var Render = (R = new Render_Views['table']());var Engine = (E = new (function(){
	var T = this;
	var _items = []; // all the items from the last query (all the current items)
	var _filtered = []; /* the filtered items */
	var _status = []; /* global status */
	var _menu = {}; // the global menu cache
	var _history = {'back': [], 'fwd': []};
	var _up = false;
	
	/* protected functions */
	var draw_menu_for_item_callback, _copycut, print_history, sync;
	
	T.address = false; // the address of current directory
	
	T.copied = false; // if something is copied (or cut)
	T.op = 'copy'; // what operation will be done - cut or copy?
	
	T.get_global_items = function() // function that gets _items
	{
		return _items;
	}
	
	T.get_filtered_items = function()
	{
		return _filtered;
	}
	
	T.set_filtered_items = function(filt)
	{
		_filtered = filt;
	}
	
	T.get_global_menu = function() // function that gets _menu
	{
		return _menu;
	}
	
	T.get_global_status = function()
	{
		return _status;
	}
	
	var _draw_timeout = false;
	
	/* cancel delayed draw of menu (e.g. information about file) */
	
	T.cancel_draw = function()
	{
		if(!_draw_timeout) return;
		
		clearTimeout(_draw_timeout);
		_draw_timeout = false;
	}
	
	// function changes dir to the specified location
	// if nohistory set to true, nothing will be added to history
	// function changes the name in the header, the address and even the menu
	
	var _lasterr = false;
	
	T.go2 = function(where,nohistory)
	{
		D.qr('index.php?act=filelist', {DIR: where}, function(res,err)
		{
			if(!res['error'])
			{
				if(!nohistory) T.add_to_history(res['DIR']);
				
				_items = res['items']; // create a var with all the types of objects
				_up = res['up'];
				_menu = {0: 'fsearch', 1: 'common',2: res['info'] };
				
				if(!nohistory || L._search_str == L._search_str_default) L._search_str = ''; // clearing the search string
				R.filter(L._search_str);
				
				
				L.draw(_menu);
				
				T.address = res['DIR'];
				I.change_path(res['DIR'],res['dir'],res['type']);
				I.change_status(_status = [['Objects',_items.length],['Size',res['size']],['Generation time',res['stats']['seconds']+'sec']]);
				I.disable_buttons();
				
				_lasterr = false;
				if(err) alert(err);
			}else if(!_lasterr) /* prevent from infitite asking */
			{
				_lasterr = true;
				alert('Could not change directory ' + res['reason']);
				if(err) alert(err);
				if(!res['stop']) T.go2(res['dir'],true);
			}
		});
	}
	
	// function is the analog of basename() function in PHP
	
	T.basename = function(path)
	{
		var p = path.split('/');
		
		return p[p.length - 1];
	}
	
	// function gets the extension of file
	
	T.get_extension = function(file)
	{
		var arr=file.split('.');
		if(!arr[1]) return '';
		for(var k in arr) var ext=arr[k];
		return ext;
	}
	
	// function that returns the full path for object with number k
	
	T.path = function(k)
	{
		/* R._selected = [_items[k]]; */
		
		return _items[k]['fullpath'];
	}
	
	// function that draws menu for 1 selected item
	// it draws the menu after a timeout of 300 msec (to enable normal double-clicking)
	
	T.draw_menu_for_item = function(item)
	{
		if(_items[item]['type']!=1&&_items[item]['type']!=0) return draw_menu_for_item_callback(item);
		
		T.cancel_draw();
			
		_draw_timeout = setTimeout(function(){draw_menu_for_item_callback(item);}, 300);
	}
	
	T.draw_menu_for_items = function()
	{	
		L.draw({0: 'operations', 1: {name: 'details', filename: 'Selected:', selnum: R.get_selected_items().length}});
		I.change_status([['Selected items',R.get_selected_items().length]]);
	}
	
	
	/* item - number of item in _items
	   info - the cached result of index.php?act=info , if present */
	draw_menu_for_item_callback = function(item, info)
	{
		var i = _items[item];
		
		if(i['type']==1||i['type']==0) 
		{
			T.cancel_draw();
			
			var dr = function(){
				if(i['type']==0) info['size']=i['size'];
				L.draw({0: 'operations',1: info });
				
				I.change_status([['Name',info['filename']],['Type',info['type']],['Size',info['size']]]);
			}
			
			if(!info) D.qr('index.php?act=info', i, function(d,err) { info = d; dr(); });
			else dr();

		}/*else if(i['type']==0)
		{
			L.draw({0: 'operations',1: {name: 'details', filename: i['name'], dir: true, size: i['size']}});
		}*/else if(i['type']==2)
		{	
			L.draw({0: 'common', 1: { name: 'details', filename: i['name'], dir: false, type: i['descr'], free: i['free'], total: i['total'], fs: i['fs'] }});
		}
	}
	
	// function that deletes the selected item (for ex. file or folder).
	
	T.delete_item = function()
	{
		var i = R.get_selected_items()[0];
		if(i['type']!=0 && i['type']!=1 || !confirm('Do you really want to delete that ' + (i['type']==1 ? 'file' : 'folder') + '?')) return; // you can only delete files and folders
		
		D.qr('index.php?act=delete', i, function(res,err)
		{
			if(res && res['success']) T.refresh();
			else alert('The item ' + i['name'] + ' could not be deleted.' + (res['reason']||err));
		},true, 'deleting...');
	}
	
	T.delete_items = function()
	{
		var items = R.get_selected_items();
		for(var i=0; i<items.length; i++)
		{
			if(items[i]['type']!=0 && items[i]['type']!=1) return; // you can only delete files and folders
		}
		
		if(!confirm('Do you really want to delete all '+items.length+' items?')) return;
		
		D.qr('index.php?act=delete', {'items':items}, function(res,err)
		{
			if(res && res['success']) T.refresh();
			else alert('Items could not be deleted.' + (res['reason']||err));
		},true, 'deleting...');
	}
	
	sync = function(el,i,e,force)
	{
		var v = $('__vary');
		e = e||window.event;
		
		if(!v)
		{
			v = document.createElement('div');
			v.className = 'norm';
			v.style.visibility = 'hidden';
			v.style.position = 'absolute';
			v.style.whiteSpace = 'pre';
			
			document.body.appendChild(v);
		}
		
		if(e && e.keyCode == 13 /* Enter */ || force)
		{
			D.qr('index.php?act=rename', {'old': i, 'new': el.value}, function(res,err)
			{
				if(res['success'])
				{
					_items[i['id']] = res['new'];
				}else
				{
					alert('The item ' + res['f'] + ' could not be renamed.' + res['reason']);
				}
				
				el.parentNode.removeChild(el);
				R.draw(_items);
				R.cl($('it'+i['id']),{});
			});
			
			el.onblur = function(){}; /* Safari blurs the element when the node is deleted and tries to rename file 2 times */
			
			return;
		}
		
		v.innerHTML = el.value;
		el.style.width = (v.clientWidth - (-20) ) + 'px';
	}
	
	// function that renames the selected item (for ex. file or folder)
	
	T.rename_item = function()
	{
		var i = R.get_selected_items()[0];
		if(i['type']!=0 && i['type']!=1) return; // you can only rename files and folders
		/*var n = prompt('Enter new name: ',i['name']);
		if(!n) return;
		*/
		
		var el = $('it'+i['id']);
		var nm = el.firstChild.nextSibling;
		
		/*
		I.dbg('name: ' + el.nodeValue);
		
		*/
		
		el.removeChild(nm);
		
		var inp = document.createElement('input');
		
		/*var buf = '';
		
		for(var k in inp)
		{
			buf += k + '<br>';
		}
		
		I.dbg(buf);
		*/
		
		var s = function(e){sync(inp,i,e);};
		
		var p = {type: 'text', value: i['name'], className: 'norm rename_inp', onkeydown: s, onblur: function(){sync(inp,i,null,true);} };
		
		for(var k in p) inp[k] = p[k];
		
		el.appendChild(inp);
		
		s();
		inp.select();
		
		R.un_cl();
	}
	
	// T function creates folder
	
	T.mkdir = function()
	{
		/* stupid IE7! It blocks prompts */
		var new_name = prompt('Enter the new directory name:','NewFolder');
		
		if(!new_name) return;
		
		D.qr('index.php?act=mkdir', {name: new_name}, function(res,err){
			if(res['success']) T.refresh();
			else alert('Could not create directory.' + res['reason']/* + '. Error text: ' + err*/);
		});
	}
	
	T.mkfile = function()
	{
		/* stupid IE7! It blocks prompts */
		var new_name = prompt('Enter the new filename:','NewFile');
		
		if(!new_name) return;
		
		D.qr('index.php?act=mkfile', {name: new_name, confirm: 0}, function(res,err){
			if(res['exists'])
			{
				if(confirm('The file already exists. Overwrite it?')) D.qr('index.php?act=mkfile', {name: new_name, confirm: 1}, function(r,e)
				{
					if(!r['success']) alert('Could not create file.' + r['reason']);
					else T.refresh();
				});
				
				return;
			}
			if(res['success']) T.refresh();
			else alert('Could not create file.' + res['reason']/* + '. Error text: ' + err*/);
		});
	}
	
	// T function downloads the selected element
	
	T.download_file = function(i)
	{
		var undef;
		if(typeof(i) == typeof(undef)) var i = R.get_selected_items()[0];
		D.qr('index.php?act=download_get_href',i,function(res,err)
		{
			if(res) window.location.href = res['href'];
			else alert('Could not get address to download file. This error cannot happen.');
		},false,'downloading...');
	}
	
	T.copy_items = (T.copy_item = function(){ _copycut('copy'); });
	
	T.cut_items = (T.cut_item = function(){ _copycut('cut'); });
	
	// the function copies or cuts the file
	
	_copycut = function (what /* copy or cut? */)
	{
		D.qr('index.php?act='+what,{items: R.get_selected_items()},function(res,err)
		{
			if(!res) alert('Could not '+what+' files.');
			else
			{
				T.op = what;
				T.copied = true;
				R.un_cl(true);
			}
		});
	}
	
	// function which pastes copied or cut items
	
	T.paste_items = function ()
	{
		D.qr('index.php?act=paste',{}, function(res,err)
		{
			if(!res) alert(err);
			T.copied = false;
			T.refresh();
		},true, T.op=='copy'?'copying...':'moving...');
	}
	
	T.cancel_advanced_paste = false;
	T.advanced_paste = function(bt)
	{
		var bytes = bt||'0 bytes';
		D.qr('index.php?act=advanced_paste',{}, function(res,err)
		{
			if(!res) res = {state: 0};
			if(res['state'] == 0)
			{
				T.copied=false;
				T.refresh();
			}else if(!T.cancel_advanced_paste)
			{
				T.advanced_paste(res['bytes']);
			}else
			{
				T.cancel_advanced_paste=false;
				T.cancel_copy();
			}
			if(err) alert(err);
			
		}, true, 'Copying ('+bytes+')... <b><u><a href="#" onclick="E.cancel_advanced_paste=true;this.innerHTML=\'cancelling...\';return false;" style="color: green;">cancel operation</a></u></b>');
	}
	
	T.cancel_copy = function ()
	{
		D.qr('index.php?act=cancel_copy',{}, function(res,err)
		{
			T.copied = false;
			T.refresh();
		});
	}
	
	// refresh filelist
	T.refresh = function(){T.go2(T.address,true);}
	
	T.edit_file_for_item = function(k)
	{
		var item = _items[k];
		
		D.qr('index.php?act=info', item, function(res,err)
		{
			var img = res['thumb'] ? true : false;
			res['thumb'] = false; /* decrease server load, we do not need to draw the thumb in info */
			
			draw_menu_for_item_callback(k, res);
			
			if(res['size_bytes'] >= 100*1024 && !img)
			{
				T.download_file(item);
			}else
			{
				try
				{
					I.window_open('index.php?act=edit&file=' + res['filename_encoded'] + (img ? '&img=true' : ''), 'edit' + res['md5(filename)'], 640, 480);
					
				}catch(e)
				{
					alert('Disable your popup blocker in order to edit files.');
				}
			}
		},true,'opening...');
	}
	
	var _block_back = false; // block add button
	var _block_fwd = false; // block fwd button
	
	T.add_to_history = function(dir)
	{
		_history['back'].push(dir);
		_history['fwd'] = [];
		//print_history();
	}
	
	T.go_back = function()
	{
		if(_history['back'].length<=1) return false;
		_history['fwd'].push(_history['back'].pop());
		T.go2(_history['back'][_history['back'].length-1], true);
		//print_history();
	}
	
	T.go_fwd = function()
	{
		if(_history['fwd'].length==0) return false;
		var addr = _history['fwd'].pop();
		_history['back'].push(addr);
		T.go2(addr, true);
		//print_history();
	}
	
	T.can_go_back = function(){ return _history['back'].length > 1; }
	T.can_go_fwd = function(){ return _history['fwd'].length > 0; }
	T.can_go_up = function(){ return _up!=false; }
	
	print_history = function()
	{
		var tmp = '<table onclick="T.style.display=\'none\'"><tr><td>Back: ';
		for(k in _history['back']) if(k!='copy') tmp+='<br>' + k + ': ' + _history['back'][k];
		tmp+='</td><td>Fwd: ';
		for(k in _history['fwd']) if(k!='copy') tmp+='<br>' + k + ': ' + _history['fwd'][k];
		tmp+='</td></tr></table>';
		
		I.dbg(tmp);
	}
	
	// function uploads files selected in the left menu
	
	T.upload_files = function()
	{
		D.qr('index.php?act=upload',{ 'form': $('upload_form'), 'DIR': T.address },function(res, err)
		{
			
			setTimeout(function(){I.show_upload();T.refresh();}, 100); // "fixing" the JsHttpRequest library bug
			
			if(!res) alert(err);
		
		},true,'uploading...');
		
		return true;
	}
	
	// the function replaces the link "show dir size" by the directory size
	
	T.show_dir_size = function(nolimit)
	{
		var el = $('_dirsize');
		var i, name;
		if(R.get_selected_items().length>0)
		{
			i = R.get_selected_items()[0];
			name = _items[i['id']]['fullpath'];
		}else
		{
			i = -1;
			name = T.address;
		}
		
		el.innerHTML = '<i>loading, please wait...</i>';
		
		D.qr('index.php?act=dirsize',{'file': name, 'nolimit': nolimit?'true':'false'}, function(res,err)
		{
			if(res)
			{
				if(!nolimit&&res[0]=='&'/*&gt; size*/) res += ' <a href="javascript:E.show_dir_size(true);" style="text-decoration: underline;">recount w/o limits</a>';
				el.innerHTML = res;
				if(i!=-1) _items[i['id']]['size'] = res;
				else _menu[2]['size'] = res;
			}
			else el.innerHTML = 'error: '+err;
		});
	}
	
	T.chmod_item = function()
	{
		var i = R.get_selected_items()[0];
		
		D.qr('index.php?act=get_rights', i, function(res,err)
		{
			if(res)
			{
				var mod = prompt('Enter new file rights', res);
				if(!mod) return;
				var recursive = i['type'] == 0 ? confirm('CHMOD items recursively (chmod also subdirectories and files in subdirectories)?') : false;
				
				D.qr('index.php?act=set_rights', {'fullpath': i['fullpath'], 'mod': mod, 'recursive': (recursive?'true':'false')},function(res,err)
				{
					if(err) alert(err);
					else T.draw_menu_for_item(i.k||i.id);
				});
			}
		});
	}
	
	T.chmod_items = function()
	{
		var mod = prompt('Enter rights for items: ', '777');
		var recursive = confirm('CHMOD items recursively (chmod also subdirectories and files in subdirectories)?');
		if(!mod) return;
				
		D.qr('index.php?act=set_rights', {items: R.get_selected_items(), 'mod': mod, 'recursive': recursive},function(res,err)
		{
			if(err) alert(err);
			E.refresh();
		});
	}
	
	T.zip_items = (T.zip_item = function()
	{	
		D.qr('index.php?act=zip', {items: R.get_selected_items()}, function(res,err)
		{
			if(err) alert(err);
			T.refresh();
		});
	});
	
	T.unzip_item = function(mode)
	{
		var i = R.get_selected_items()[0];
		
		D.qr('index.php?act=unzip', {'fullpath': i['fullpath'], 'mode': mode}, function(res,err)
		{
			if(err) alert(err);
			T.refresh();
		});
	}
	
	T.run_update = function()
	{
		D.qr('index.php?act=update', {}, function(res,err)
		{
			if(!res)
			{
				if(confirm('Auto-update failed.\nDo you want to use advanced way to update Dolphin.php (version will be changed to light)?')) window.location='index.php?version=light&DIR=.&act=download-new';
			}
			else
			{
				alert('Update successful!');
				window.location.reload();
			}
		});
	}
	
	// the function which opens the terminal window
	
	T.open_terminal = function()
	{
		I.window_open('index.php?act=terminal', 'terminal', 700, 500);
	}
})());
LeftMenu = (L = new (function()
{
	var T = this;
	var names = {}; // the names of menus { id: name, ... }
	var hidden = {}; // the names of hidden elements { 'name': name, ... }
	
	var add_link, opac; /* protected functions */
	
	// generates left menu with the specified parameters:
	// params = { id1: 'what to draw1', id2: 'what to draw2', ... }
	// 'what to drawN' = 'common' || 'additional' || 'operations' || 'long text'
	
	T.draw = function(params)
	{
		var i=0;
		var tmp='',header='',body='',up='',visible='';
		names = {};
		
		for(var k in params)
		{
			i++;
			
			if(!params[k]['name']) params[k]={name: params[k]};
			
			names[i]=params[k]['name'];
			
			var p = params[k];
			
			switch(p['name'])
			{
			default:
			case 'common':
				header='Common';
				body='';
				if(E.copied)
				{
					body+=add_link("javascript:E.paste_items();",'Paste items here','paste','Paste');
					/*if(E.op=='copy') body+=add_link("javascript:E.advanced_paste();",'Paste items in several steps','paste','Paste <i>big files, experimental</i>');*/
					body+=add_link("javascript:E.cancel_copy();",'Cancel '+E.op,'cancel','Cancel '+E.op);
				}
				body+=add_link("javascript:E.mkfile();",'Create a file','mkdir','Create a file');
				body+=add_link("javascript:E.mkdir();",'Create a folder','mkdir','Create a directory');
				body+=add_link("javascript:E.open_terminal();",'Open terminal window to execute shell commands','rename','Open terminal');
				/* TODO: make a better uploads */
				body+=add_link("javascript:I.show_upload();",'Upload files','upload','Upload files');
				body+='<form enctype="multipart/form-data" style="display:none; margin: 0px; padding: 0px;" id="upload_form"><div id="uploads_container"></div><div align="right"><a href="javascript:I._append_upload();" style="text-decoration: underline;">add more files...</a></div><input type="button" style="font-size: 10px; width: 50px;" onclick="E.upload_files();" value="upload" /></form>';
				break;
			case 'fsearch':
				header='Filename filter';
				T._search_str_default = 'Enter part of filename...';
				if(!T._search_str) T._search_str = T._search_str_default; // the search string
				body='<input type=text name="fsearch" id="fsearch" class="fsearch_g" onkeyup="L._search_str=this.value;R.filter(this.value);" onfocus="if(this.value==\''+T._search_str_default+'\') this.value=\'\';this.className=\'fsearch\'" onblur="this.className=\'fsearch_g\';if(this.value==\'\') this.value=\''+T._search_str_default+'\';" value="'+T._search_str+'">';
				break;
			case 'operations': // all items are taken from the main frame
				var s /* selected */ = R._selected;
				
				header='Tasks for files and folders';
				
				if(!s[1])
				{
					s = s[0];
					
					if(s['type'] == 1)
					{
						body = add_link("javascript:E.rename_item();",'Set another name to current file','rename','Rename file');
						body += add_link("javascript:E.cut_item();",'Move file to another place','cut','Cut file');
						
						body += add_link("javascript:E.copy_item();",'Make a copy of file','copy','Copy file');
						
						body += add_link("javascript:E.download_file();",'Download the selected file to your computer','upload','Download file');
						
						body += add_link("javascript:E.delete_item();",'Remove the file from computer','delete','Delete file');
						if(E.get_extension(s['fullpath']) == 'zip')
						{
							body += add_link("javascript:E.unzip_item(&quot;extract_here&quot;);",'Extract contents here','zip','Extract here');
							var lon = E.basename(s['fullpath']);
							lon = lon.substr(0, lon.length-4);
							var shor = lon.length>12 ? lon.substr(0,9) + '...' : lon;
							
							body += add_link("javascript:E.unzip_item(&quot;extract&quot;);",'Extract to &quot;'+lon+'/&quot;','zip','Extract to &quot;' + shor + '/&quot;');
						}else
						{
							body += add_link("javascript:E.zip_item();",'Add file to zip','zip','Add to zip');
						}
						
						body += add_link("javascript:E.chmod_item();",'Change rights of file','admin','CHMOD file');
					}else
					{
						body = add_link("javascript:E.rename_item();",'Set another name to current directory','rename','Rename folder');
						body += add_link("javascript:E.cut_item();",'Move directory to another place','cut','Cut folder');
						
						body += add_link("javascript:E.copy_item();",'Make a copy of directory','copy','Copy folder');
						
						body += add_link("javascript:E.delete_item();",'Remove the directory from computer','delete','Delete folder');
						body += add_link("javascript:E.zip_item();",'Add directory to zip','zip','Add to zip');
						
						body += add_link("javascript:E.chmod_item();",'Change rights of directory','admin','CHMOD dir');
					}
				}else
				{
					body += add_link("javascript:E.cut_items();",'Move items to another place','cut','Cut items');
					
					body += add_link("javascript:E.copy_items();",'Make copy of items','copy','Copy items');
					
					/*body += add_link("javascript:E.download_files();",'Download the selected items to your computer','upload','Download file');*/
					
					body += add_link("javascript:E.delete_items();",'Remove the items from computer','delete','Delete items');
					/*if(E.get_extension(s['fullpath']) == 'zip')
					{
						body += add_link("javascript:E.unzip_item(&quot;extract_here&quot;);",'Extract contents here','zip','Extract here');
						var lon = E.basename(s['fullpath']);
						var lon = lon.substr(0, lon.length-4);
						shor = lon.length>12 ? lon.substr(0,9) + '...' : lon;
						
						body += add_link("javascript:E.unzip_item(&quot;extract&quot;);",'Extract to &quot;'+lon+'/&quot;','zip','Extract to &quot;' + shor + '/&quot;');
					}else
					{
						
					}
					*/
					
					body += add_link("javascript:E.zip_items();",'Add items to zip','zip','Add to zip');
					
					body += add_link("javascript:E.chmod_items();",'Change rights of items','admin','CHMOD items');
				}
				break;
			case 'details': // params: { filename, dir, type, changed, size, thumb }
				header='Details';
				
				if(p['thumb']) body=p['thumb'];
				else body='';
				
				body+='<b style="'+(document.all && !window.opera /* stupid IE doesn't understand, what does the overflow of element without width mean */ ? 'width: 170px;' : '')+'overflow: hidden; display: block;">'+p['filename']+'</b>';
				
				if(p['dir']) p['type'] = 'Directory';
				
				if(p['type']) body+=p['type'] + '<br><br>';
				else body += '<br>';
				
				if(p['selnum']) body+=p['selnum'] + ' items<br><br>';
				
				if(p['id3']) body+=p['id3']+ '<br><br>';
				
				if(p['fs']) body+='Filesystem: ' + p['fs'] + '<br><br>';
				if(p['free']) body+='Free disk space: ' + p['free']+ '<br><br>';
				if(p['total']) body+='Total disk space: ' + p['total']+ '<br><br>';				
				
				if(p['changed']) body+='Changed: '+p['changed']+ '<br><br>';
				if(p['owner']) body+='Owner: '+p['owner']+'<br><br>';
				if(p['group']) body+='Group: '+p['group']+'<br><br>';
				if(p['rights']) body+='Rights: '+p['rights']+'<br><br>';
				
				if(p['size']) body+='Size: <span id="_dirsize">'+p['size']+ '</span><br><br>';
				else if(p['dir']) body+='Size: <span id="_dirsize"><a href="javascript:E.show_dir_size(false);" style="text-decoration: underline;">click to show size</a></span>'+ '<br><br>';
				
				body = body.substr(0,body.length-4);
				if(body.substr(body.length,body.length-4) == '<br>') body = body.substr(0,body.length-4);;
				
				break;
			case 'long text':
				header='phylosophy';
				body='long text should be here';
				/*
				body='Харизматическое лидерство потенциально. Принимая во внимание позицию Ф. Фукуямы, политическое учение Локка постоянно. Социальная парадигма формирует тоталитарный тип политической культуры, о чем писали такие авторы, как Н. Луман и П. Вирилио. Структура политической науки, несмотря на внешние воздействия, предсказуема.<br><br>\
	Политическое учение Н. Макиавелли важно приводит гносеологический культ личности, что неминуемо повлечет эскалацию напряжения в стране. Информационно-технологическая революция существенно отражает экзистенциальный постиндустриализм, указывает в своем исследовании К. Поппер. Либеральная теория интегрирует онтологический марксизм, исчерпывающее исследование чего дал М. Кастельс в труде "Информационная эпоха". Политическая коммуникация отражает институциональный марксизм (отметим, что это особенно важно для гармонизации политических интересов и интеграции общества). Политическое учение Н. Макиавелли доказывает культ личности (терминология М. Фуко).';
	*/
				break;
			}
			
			var up = hidden[p['name']] ? 'l_darr' : 'l_uarr';
			var displ = up=='l_uarr' ? '' : ' style="display: none;"'
			
			tmp+='<table width="100%" cellpadding="0" cellspacing="0" border=0>\
	\
	<tr height=12><td colspan=4><img src="f/i/no.png" width=1 height=1></td></tr> <!--spacer-->\
	\
	<tr height=23 id="h'+i+'" style="color: #3f3d3d;">\
	<td width=12><img src="f/i/no.png" width=1 height=1></td><td class="left_menu_head" onmouseover="L._highlight('+i+',\'over\');" onmouseout="L._highlight('+i+',\'out\');" onclick="L._hide('+i+');">'+header+'</td><td width=23 onmouseover="L._highlight('+i+',\'over\');" onmouseout="L._highlight('+i+',\'out\');" onclick="L._hide('+i+');"><img src="f/i/no.png" width=23 height=23 id="i'+i+'" style="background: url(\'f/i/overall.78.png\'); background-position: -' + I.coords[up][0] + 'px -' + I.coords[up][1] + 'px;"></td><td width=12><img src="f/i/no.png" width=12 height=1></td>\
	</tr>\
	\
	</table><table width="100%" cellpadding="0" cellspacing="0" id="b'+i+'" border=0'+displ+'>\
	<tr>\
	<td width=12><img src="f/i/no.png" width=12 height=1></td><td colspan=2 class="left_menu_body">'+body+'</td><td width=12><img src="f/i/no.png" width=12 height=1></td>\
	</tr>\
	</table>';
		}
		
		tmp += '<table width="100%" cellpadding="0" cellspacing="0" border=0>\
	\
	<tr height=12><td colspan=4><img src="f/i/no.png" width=1 height=1></td></tr> <!--spacer-->\
	\
	</table>';
		
		$('left_menu_div').innerHTML=tmp;
	}
	
	// function is used to highlight the header in the left menu (used in draw)
	
	T._highlight = function(id,act)
	{
		var el = $('i'+id);
		var obj = $('h'+id);
		var state = act=='over' ? 'h' : '';
		
		if(hidden[names[id]]) I.im(el,state,'l_darr');
		else I.im(el,state,'l_uarr');
		
		if(act=='over')
		{
			obj.style.color = '#7e7c7c';
			obj.style.cursor = 'pointer';
		}else
		{
			obj.style.color = '#3f3d3d';
			obj.style.cursor = 'default';
		}
	}
	
	// function is used to hide left menu with the specified id (used in draw_left_menu)
	
	T._hide = function(id)
	{
		var el = $('b'+id);
		var img = $('i'+id);
		var name = names[id];
		
		if(el.style.display!='none')
		{
			opac(el,0.3,false);
			
			setTimeout(function(){el.style.display='none';},350);
			I.im(img,'h','l_darr');
			
			hidden[name] = name;
		}else
		{
			el.style.visibility = 'hidden';
			el.style.display='';
			
			opac(el,0.3,true);
			
			I.im(img,'h','l_uarr');
			
			hidden[name] = null;
		}
	}
	
	// this function adds link with icon [icon] (see menu_all.png and coords), href [href], with title [title] and name [name]
	
	var _i = 0; // the counter for add_link
	
	add_link = function(href,title,icon,name)
	{
		_i++;
		var style = "background: url('f/i/menu_all.png') -"+I.coords['m_'+icon][0]+"px -"+I.coords['m_'+icon][1]+"px";
		
		return '<div style="padding-top: 2px; padding-bottom: 2px;"><a href="'+href+'" title="'+title+'" onmouseover="L._underl('+_i+',true);" onmouseout="L._underl('+_i+',false);"><img src="f/i/no.png" width=16 height=16 style="'+style+'" align=absmiddle border=0>&nbsp;&nbsp;<span id=\'u'+_i+'\'>'+name+'</span></a></div>';
	}
	
	//a function that is used in "add link" to underline links
	
	T._underl = function(id,underline)
	{
		var el=$('u'+id);
		
		if(underline) el.style.textDecoration='underline';
		else el.style.textDecoration='none';
	}
	
	// function for opacity
	
	opac = function(el,duration,direct)
	{
		if(!duration) var duration=0.3;
		if(direct==undefined) var direct = true; // true - show element, false - hide element
		
		if(el.runtimeStyle) //IE, filter works only with absolute positioned elements, or elements with specified width or height. Other elements are just _made_ to answer there conditions
		{
			if(el.style.position!='absolute' && !el.style.width && !el.style.height)
			{
				el.style.width=el.offsetWidth;
				el.style.height=el.offsetHeight;
			}
			
			el.runtimeStyle.filter='BlendTrans(Duration='+duration+')';
			
			if(direct) el.style.visibility = "hidden";
			else el.style.visibility = "visible";
			
			el.filters["BlendTrans"].Apply();
			
			if(!direct) el.style.visibility = "hidden";
			else el.style.visibility = "visible";
			
			el.filters["BlendTrans"].Play();
			return true;
		}
		
		if(el.style.opacity!=undefined) //Mozilla and opera >= 9
		{
			var bit=-1/(duration*40);
			
			if(!direct) bit = -bit;
			
			el.style.opacity=direct ? 0 : 1;
			el.style.visibility="visible";
			var op=function()
			{
				if((el.style.opacity>=1 && direct) || (el.style.opacity<=0 && !direct)) return;
				el.style.opacity-=bit; //fucky "+" works like if a digit was a string
				
				setTimeout(op,25);
			}
			op();
			return true;
		}
		
		return false;
	}
})());Interface = (I = new (function(){
	
	var T = this;
	
	T.coords = { // 'name': {right, top, width, height}
	/** overall.78.png **/
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
		
		if(type==0 /* 0 */)
		{
			a.style.backgroundPosition = '-0px -516px';
			h.style.backgroundPosition = '-37px -360px';
		}else if(type==2 /* 2 */)
		{
			a.style.backgroundPosition = '-0px -538px';
			h.style.backgroundPosition = '-37px -390px';
		}else if(type==3 /* 3 */)
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
		
		el.innerHTML = tmp.join('&nbsp;<img src="f/i/no.png" width=8 height=27 border=0 style="padding: 0px; margin: 0px; background: url(\'f/i/overall.78.png\'); background-position: -85px -516px;" align="absmiddle">&nbsp;');
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
			tmp += '<img id="btn_' + k + '" src="f/i/no.png" onmouseover="I.im(this,\'h\');" onmouseout="I.im(this,\'\');" onmousedown="I.im(this,\'d\');" onmouseup="I.im(this,\'h\'); ' + act[k] + ' " alt="' + lang[k] + '" title="' + lang[k] + '" style="background: url(\'f/i/overall.78.png\'); background-position: -' + coords[k][0] + 'px -' + coords[k][1] + 'px;' + (k.substr(k.length-9,9)=='_disabled' ? 'display: none;' : '') + '" width="' + coords[k][2] + '" height="' + coords[k][3] + '" />';
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
			
			D.qr('http://dolphin-php.org/'+'build-info/', {}, function(res,err)
			{
				if(!res) alert('Could not contact http://dolphin-php.org/.');
				else if(res == 78) alert('No new version available');
				else if(res < 78) alert('You have a newer version, than on a server :).');
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
})());/* this file should be included last */

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
					alert('AJAX request failed because of 404 error (Not Found). Please ensure, that Dolphin.php is installed properly.');
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
})());/**
 * JsHttpRequest: JavaScript "AJAX" data loader
 *
 * @license LGPL
 * @author Dmitry Koterov, http://en.dklab.ru/lib/JsHttpRequest/
 * @version 5.x $Id$
 */

// {{{
function JsHttpRequest() {
    // Standard properties.
    var t = this;
    t.onreadystatechange = null;
    t.readyState         = 0;
    t.responseText       = null;
    t.responseXML        = null;
    t.status             = 200;
    t.statusText         = "OK";
    // JavaScript response array/hash
    t.responseJS         = null;

    // Additional properties.
    t.caching            = false;        // need to use caching?
    t.loader             = null;         // loader to use ('form', 'script', 'xml'; null - autodetect)
    t.session_name       = "PHPSESSID";  // set to  cookie or GET parameter name

    // Internals.
    t._ldObj              = null;  // used loader object
    t._reqHeaders        = [];    // collected request headers
    t._openArgs          = null;  // parameters from open()
    t._errors = {
        inv_form_el:        'Invalid FORM element detected: name=%, tag=%',
        must_be_single_el:  'If used, <form> must be a single HTML element in the list.',
        js_invalid:         'JavaScript code generated by backend is invalid!\n%',
        url_too_long:       'Cannot use so long query with GET request (URL is larger than % bytes)',
        unk_loader:         'Unknown loader: %',
        no_loaders:         'No loaders registered at all, please check JsHttpRequest.LOADERS array',
        no_loader_matched:  'Cannot find a loader which may process the request. Notices are:\n%',
        no_headers:         'Method setRequestHeader() cannot work together with the % loader.'
    }
    
    /**
     * Aborts the request. Behaviour of this function for onreadystatechange() 
     * is identical to IE (most universal and common case). E.g., readyState -> 4
     * on abort() after send().
     */
    t.abort = function() { with (this) {
        if (_ldObj && _ldObj.abort) _ldObj.abort();
        _cleanup();
        if (readyState == 0) {
            // start->abort: no change of readyState (IE behaviour)
            return;
        }
        if (readyState == 1 && !_ldObj) {
            // open->abort: no onreadystatechange call, but change readyState to 0 (IE).
            // send->abort: change state to 4 (_ldObj is not null when send() is called)
            readyState = 0;
            return;
        }
        _changeReadyState(4, true); // 4 in IE & FF on abort() call; Opera does not change to 4.
    }}
    
    /**
     * Prepares the object for data loading.
     */
    t.open = function(method, url, asyncFlag, username, password) { with (this){
        // Append  to original URL. Use try...catch for security problems.
        try {
            if (
                document.location.search.match(new RegExp('[&?]' + session_name + '=([^&?]*)'))
                || document.cookie.match(new RegExp('(?:;|^)\\s*' + session_name + '=([^;]*)'))
            ) {
                url += (url.indexOf('?') >= 0? '&' : '?') + session_name + "=" + this.escape(RegExp.$1);
            }
        } catch (e) {}
        // Store open arguments to hash.
        _openArgs = {
            method:     (method || '').toUpperCase(),
            url:        url,
            asyncFlag:  asyncFlag,
            username:   username != null? username : '',
            password:   password != null? password : ''
        }
        _ldObj = null;
        _changeReadyState(1, true); // compatibility with XMLHttpRequest
        return true;
    }}
    
    /**
     * Sends a request to a server.
     */
    t.send = function(content) {
        if (!this.readyState) {
            // send without open or after abort: no action (IE behaviour).
            return;
        }
        this._changeReadyState(1, true); // compatibility with XMLHttpRequest
        this._ldObj = null;
        
        // Prepare to build QUERY_STRING from query hash.
        var queryText = [];
        var queryElem = [];
        if (!this._hash2query(content, null, queryText, queryElem)) return;
    
        // Solve the query hashcode & return on cache hit.
        var hash = null;
        if (this.caching && !queryElem.length) {
            hash = this._openArgs.username + ':' + this._openArgs.password + '@' + this._openArgs.url + '|' + queryText + "#" + this._openArgs.method;
            var cache = JsHttpRequest.CACHE[hash];
            if (cache) {
                this._dataReady(cache[0], cache[1]);
                return false;
            }
        }
    
        // Try all the loaders.
        var loader = (this.loader || '').toLowerCase();
        if (loader && !JsHttpRequest.LOADERS[loader]) return this._error('unk_loader', loader);
        var errors = [];
        var lds = JsHttpRequest.LOADERS;
        for (var tryLoader in lds) {
            var ldr = lds[tryLoader].loader;
            if (!ldr) continue; // exclude possibly derived prototype properties from "for .. in".
            if (loader && tryLoader != loader) continue;
            // Create sending context.
            var ldObj = new ldr(this);
            JsHttpRequest.extend(ldObj, this._openArgs);
            JsHttpRequest.extend(ldObj, {
                queryText:  queryText.join('&'),
                queryElem:  queryElem,
                id:         (new Date().getTime()) + "" + JsHttpRequest.COUNT++,
                hash:       hash,
                span:       null
            });
            var error = ldObj.load();
            if (!error) {
                // Save loading script.
                this._ldObj = ldObj;
                JsHttpRequest.PENDING[ldObj.id] = this;
                return true;
            }
            if (!loader) {
                errors[errors.length] = '- ' + tryLoader.toUpperCase() + ': ' + this._l(error);
            } else {
                return this._error(error);
            }
        }
    
        // If no loader matched, generate error message.
        return tryLoader? this._error('no_loader_matched', errors.join('\n')) : this._error('no_loaders');
    }
    
    /**
     * Returns all response headers (if supported).
     */
    t.getAllResponseHeaders = function() { with (this) {
        return _ldObj && _ldObj.getAllResponseHeaders? _ldObj.getAllResponseHeaders() : [];
    }}

    /**
     * Returns one response header (if supported).
     */
    t.getResponseHeader = function(label) { with (this) {
        return _ldObj && _ldObj.getResponseHeader? _ldObj.getResponseHeader() : [];
    }}

    /**
     * Adds a request header to a future query.
     */
    t.setRequestHeader = function(label, value) { with (this) {
        _reqHeaders[_reqHeaders.length] = [label, value];
    }}
    
    //
    // Internal functions.
    //
    
    /**
     * Do all the work when a data is ready.
     */
    t._dataReady = function(text, js) { with (this) {
        if (caching && _ldObj) JsHttpRequest.CACHE[_ldObj.hash] = [text, js];
        if (text !== null || js !== null) {
            status = 4;
            responseText = responseXML = text;
            responseJS = js;
        } else {
            status = 500;
            responseText = responseXML = responseJS = null;
        }
        _changeReadyState(2);
        _changeReadyState(3);
        _changeReadyState(4);
        _cleanup();
    }}
    
    /**
     * Analog of sprintf(), but translates the first parameter by _errors.
     */
    t._l = function(args) {
        var i = 0, p = 0, msg = this._errors[args[0]];
        // Cannot use replace() with a callback, because it is incompatible with IE5.
        while ((p = msg.indexOf('%', p)) >= 0) {
            var a = args[++i] + "";
            msg = msg.substring(0, p) + a + msg.substring(p + 1, msg.length);
            p += 1 + a.length;
        }
        return msg;
    }

    /** 
     * Called on error.
     */
    t._error = function(msg) {
        msg = this._l(typeof(msg) == 'string'? arguments : msg)
        msg = "JsHttpRequest: " + msg;

		/* <youROCK> */
		/* add support of very useful "onerror" property */

		if(t.onerror)
		{
			return t.onerror(msg);
		}

		/* </youROCK> */

        if (!window.Error) {
            // Very old browser...
            throw msg;
        } else if ((new Error(1, 'test')).description == "test") {
            // We MUST (!!!) pass 2 parameters to the Error() constructor for IE5.
            throw new Error(1, msg);
        } else {
            // Mozilla does not support two-parameter call style.
            throw new Error(msg);
        }
    }
    
    /**
     * Convert hash to QUERY_STRING.
     * If next value is scalar or hash, push it to queryText.
     * If next value is form element, push [name, element] to queryElem.
     */
    t._hash2query = function(content, prefix, queryText, queryElem) {
        if (prefix == null) prefix = "";
        if (content instanceof Object) {
            var formAdded = false;
            for (var k in content) {
                var v = content[k];
                if (v instanceof Function) continue;
                var curPrefix = prefix? prefix + '[' + this.escape(k) + ']' : this.escape(k);
                var isFormElement = v && v.parentNode && v.parentNode.appendChild && v.tagName;
                if (isFormElement) {
                    var tn = v.tagName.toUpperCase();
                    if (tn == 'FORM') {
                        // FORM itself is passed.
                        formAdded = true;
                    } else if (tn == 'INPUT' || tn == 'TEXTAREA' || tn == 'SELECT') {
                        // This is a single form elemenent.
                    } else {
                        return this._error('inv_form_el', (e.name||''), e.tagName);
                    }
                    queryElem[queryElem.length] = { name: curPrefix, e: v };
                } else if (v instanceof Object) {
                    this._hash2query(v, curPrefix, queryText, queryElem);
                } else {
                    // We MUST skip  values, because there is no method
                    // to pass 's via GET or POST request in PHP.
                    if (v === null) continue;
                    queryText[queryText.length] = curPrefix + "=" + this.escape('' + v);
                }
                if (formAdded && queryElem.length > 1) {
                    return this._error('must_be_single_el');
                }
            }
        } else {
            queryText[queryText.length] = content;
        }
        return true;
    }
    
    /**
     * Remove last used script element (clean memory).
     */
    t._cleanup = function() {
        var ldObj = this._ldObj;
        if (!ldObj) return;
        // Mark this loading as aborted.
        JsHttpRequest.PENDING[ldObj.id] = false;
        var span = ldObj.span;
        if (!span) return;
        ldObj.span = null;
        var closure = function() {
            span.parentNode.removeChild(span);
        }
        // IE5 crashes on setTimeout(function() {...}, ...) construction! Use tmp variable.
        JsHttpRequest.setTimeout(closure, 50);
    }
    
    /**
     * Change current readyState and call trigger method.
     */
    t._changeReadyState = function(s, reset) { with (this) {
        if (reset) {
            status = statusText = responseJS = null;
            responseText = '';
        }
        readyState = s;
        if (onreadystatechange) onreadystatechange();
    }}
    
    /**
     * JS escape() does not quote '+'.
     */
    t.escape = function(s) {
        return escape(s).replace(new RegExp('\\+','g'), '%2B');
    }
}


// Global library variables.
JsHttpRequest.COUNT = 0;              // unique ID; used while loading IDs generation
JsHttpRequest.MAX_URL_LEN = 2000;     // maximum URL length
JsHttpRequest.CACHE = {};             // cached data
JsHttpRequest.PENDING = {};           // pending loadings
JsHttpRequest.LOADERS = {};           // list of supported data loaders (filled at the bottom of the file)
JsHttpRequest._dummy = function() {}; // avoid memory leaks


/**
 * These functions are dirty hacks for IE 5.0 which does not increment a
 * reference counter for an object passed via setTimeout(). So, if this 
 * object (closure function) is out of scope at the moment of timeout 
 * applying, IE 5.0 crashes. 
 */

/**
 * Timeout wrappers storage. Used to avoid zeroing of referece counts in IE 5.0.
 * Please note that you MUST write "window.setTimeout", not "setTimeout", else
 * IE 5.0 crashes again. Strange, very strange...
 */
JsHttpRequest.TIMEOUTS = { s: window.setTimeout, c: window.clearTimeout };

/**
 * Wrapper for IE5 buggy setTimeout.
 * Use this function instead of a usual setTimeout().
 */
JsHttpRequest.setTimeout = function(func, dt) {
    // Always save inside the window object before a call (for FF)!
    window.JsHttpRequest_tmp = JsHttpRequest.TIMEOUTS.s; 
    if (typeof(func) == "string") {
        id = window.JsHttpRequest_tmp(func, dt);
    } else {
        var id = null;
        var mediator = function() {
            func();
            delete JsHttpRequest.TIMEOUTS[id]; // remove circular reference
        }
        id = window.JsHttpRequest_tmp(mediator, dt);
        // Store a reference to the mediator function to the global array
        // (reference count >= 1); use timeout ID as an array key;
        JsHttpRequest.TIMEOUTS[id] = mediator;
    }
    window.JsHttpRequest_tmp = null; // no delete() in IE5 for window
    return id;
}

/**
 * Complimental wrapper for clearTimeout. 
 * Use this function instead of usual clearTimeout().
 */
JsHttpRequest.clearTimeout = function(id) {
    window.JsHttpRequest_tmp = JsHttpRequest.TIMEOUTS.c;
    delete JsHttpRequest.TIMEOUTS[id]; // remove circular reference
    var r = window.JsHttpRequest_tmp(id);
    window.JsHttpRequest_tmp = null; // no delete() in IE5 for window
    return r;
}


/**
 * Global static function.
 * Simple interface for most popular use-cases.
 * You may also pass URLs like "GET url" or "script.GET url".
 */
JsHttpRequest.query = function(url, content, onready, nocache) {
    var req = new this();
    req.caching = !nocache;
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            onready(req.responseJS, req.responseText);
        }
    }
    var method = null;
    if (url.match(/^((\w+)\.)?(GET|POST)\s+(.*)/i)) {
        req.loader = RegExp.$2? RegExp.$2 : null;
        method = RegExp.$3;
        url = RegExp.$4; 
    }
    req.open(method, url, true);
    req.send(content);
}


/**
 * Global static function.
 * Called by server backend script on data load.
 */
JsHttpRequest.dataReady = function(d) {
    var th = this.PENDING[d.id];
    delete this.PENDING[d.id];
    if (th) {
        th._dataReady(d.text, d.js);
    } else if (th !== false) {
        throw "dataReady(): unknown pending id: " + d.id;
    }
}


// Adds all the properties of src to dest.
JsHttpRequest.extend = function(dest, src) {
    for (var k in src) dest[k] = src[k];
}

/**
 * Each loader has the following properties which must be initialized:
 * - method
 * - url
 * - asyncFlag (ignored)
 * - username
 * - password
 * - queryText (string)
 * - queryElem (array)
 * - id
 * - hash
 * - span
 */ 
 
// }}}

// {{{ xml
// Loader: XMLHttpRequest or ActiveX.
// [+] GET and POST methods are supported.
// [+] Most native and memory-cheap method.
// [+] Backend data can be browser-cached.
// [-] Cannot work in IE without ActiveX. 
// [-] No support for loading from different domains.
// [-] No uploading support.
//
JsHttpRequest.LOADERS.xml = { loader: function(req) {
    JsHttpRequest.extend(req._errors, {
        xml_no:          'Cannot use XMLHttpRequest or ActiveX loader: not supported',
        xml_no_diffdom:  'Cannot use XMLHttpRequest to load data from different domain %',
        xml_no_headers:  'Cannot use XMLHttpRequest loader or ActiveX loader, POST method: headers setting is not supported, needed to work with encodings correctly',
        xml_no_form_upl: 'Cannot use XMLHttpRequest loader: direct form elements using and uploading are not implemented'
    });
    
    this.load = function() {
        if (this.queryElem.length) return ['xml_no_form_upl'];
        
        // XMLHttpRequest (and MS ActiveX'es) cannot work with different domains.
        if (this.url.match(new RegExp('^([a-z]+)://([^\\/]+)(.*)', 'i'))) {
            if (RegExp.$2.toLowerCase() == document.location.hostname.toLowerCase()) {
                this.url = RegExp.$3;
            } else {
                return ['xml_no_diffdom', RegExp.$2];
            }
        }
        
        // Try to obtain a loader.
        var xr = null;
        if (window.XMLHttpRequest) {
            try { xr = new XMLHttpRequest() } catch(e) {}
        } else if (window.ActiveXObject) {
            try { xr = new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
            if (!xr) try { xr = new ActiveXObject("Msxml2.XMLHTTP") } catch (e) {}
        }
        if (!xr) return ['xml_no'];
        
        // Loading method detection. We cannot POST if we cannot set "octet-stream" 
        // header, because we need to process the encoded data in the backend manually.
        var canSetHeaders = window.ActiveXObject || xr.setRequestHeader;
        if (!this.method) this.method = canSetHeaders? 'POST' : 'GET';
        
        // Build & validate the full URL.
        if (this.method == 'GET') {
            if (this.queryText) this.url += (this.url.indexOf('?') >= 0? '&' : '?') + this.queryText;
            this.queryText = '';
            if (this.url.length > JsHttpRequest.MAX_URL_LEN) return ['url_too_long', JsHttpRequest.MAX_URL_LEN];
        } else if (this.method == 'POST' && !canSetHeaders) {
            return ['xml_no_headers'];
        }
        
        // Add ID to the url if we need to disable the cache.
        this.url += (this.url.indexOf('?') >= 0? '&' : '?') + 'JsHttpRequest=' + (req.caching? '0' : this.id) + '-xml';        
        
        // Assign the result handler.
        var id = this.id;
        xr.onreadystatechange = function() { 
            if (xr.readyState != 4) return;
            // Avoid memory leak by removing the closure.
            xr.onreadystatechange = JsHttpRequest._dummy;
            req.status = null;
            try { 
                // In case of abort() call, xr.status is unavailable and generates exception.
                // But xr.readyState equals to 4 in this case. Stupid behaviour. :-(
                req.status = xr.status;
                req.responseText = xr.responseText;
            } catch (e) {}
            if (!req.status) return;
            try {
                // Prepare generator function & catch syntax errors on this stage.
                eval('JsHttpRequest._tmp = function(id) { var d = ' + req.responseText + '; d.id = id; JsHttpRequest.dataReady(d); }');
            } catch (e) {
                // Note that FF 2.0 does not throw any error from onreadystatechange handler.
                return req._error('js_invalid', req.responseText)
            }
            // Call associated dataReady() outside the try-catch block 
            // to pass exceptions in onreadystatechange in usual manner.
            JsHttpRequest._tmp(id);
            JsHttpRequest._tmp = null;
        };

        // Open & send the request.
        xr.open(this.method, this.url, true, this.username, this.password);
        if (canSetHeaders) {
            // Pass pending headers.
            for (var i = 0; i < req._reqHeaders.length; i++) {
                xr.setRequestHeader(req._reqHeaders[i][0], req._reqHeaders[i][1]);
            }
            // Set non-default Content-type. We cannot use 
            // "application/x-www-form-urlencoded" here, because 
            // in PHP variable HTTP_RAW_POST_DATA is accessible only when 
            // enctype is not default (e.g., "application/octet-stream" 
            // is a good start). We parse POST data manually in backend 
            // library code. Note that Safari sets by default "x-www-form-urlencoded"
            // header, but FF sets "text/xml" by default.
            xr.setRequestHeader('Content-Type', 'application/octet-stream');
        }
        xr.send(this.queryText);
        
        // No SPAN is used for this loader.
        this.span = null;
        this.xr = xr; // save for later usage on abort()
        
        // Success.
        return null;
    }
    
    // Override req.getAllResponseHeaders method.
    this.getAllResponseHeaders = function() {
        return this.xr.getAllResponseHeaders();
    }
    
    // Override req.getResponseHeader method.
    this.getResponseHeader = function(label) {
        return this.xr.getResponseHeader(label);
    }

    this.abort = function() {
        this.xr.abort();
        this.xr = null;
    }
}}
// }}}


// {{{ script
// Loader: SCRIPT tag.
// [+] Most cross-browser. 
// [+] Supports loading from different domains.
// [-] Only GET method is supported.
// [-] No uploading support.
// [-] Backend data cannot be browser-cached.
//
JsHttpRequest.LOADERS.script = { loader: function(req) {
    JsHttpRequest.extend(req._errors, {
        script_only_get:   'Cannot use SCRIPT loader: it supports only GET method',
        script_no_form:    'Cannot use SCRIPT loader: direct form elements using and uploading are not implemented'
    })
    
    this.load = function() {
        // Move GET parameters to the URL itself.
        if (this.queryText) this.url += (this.url.indexOf('?') >= 0? '&' : '?') + this.queryText;
        this.url += (this.url.indexOf('?') >= 0? '&' : '?') + 'JsHttpRequest=' + this.id + '-' + 'script';        
        this.queryText = '';
        
        if (!this.method) this.method = 'GET';
        if (this.method !== 'GET') return ['script_only_get'];
        if (this.queryElem.length) return ['script_no_form'];
        if (this.url.length > JsHttpRequest.MAX_URL_LEN) return ['url_too_long', JsHttpRequest.MAX_URL_LEN];
        if (req._reqHeaders.length) return ['no_headers', 'SCRIPT'];

        var th = this, d = document, s = null, b = d.body;
        if (!window.opera) {
            // Safari, IE, FF, Opera 7.20.
            this.span = s = d.createElement('SCRIPT');
            var closure = function() {
                s.language = 'JavaScript';
                if (s.setAttribute) s.setAttribute('src', th.url); else s.src = th.url;
                b.insertBefore(s, b.lastChild);
            }
        } else {
            // Oh shit! Damned stupid Opera 7.23 does not allow to create SCRIPT 
            // element over createElement (in HEAD or BODY section or in nested SPAN - 
            // no matter): it is created deadly, and does not response the href assignment.
            // So - always create SPAN.
            this.span = s = d.createElement('SPAN');
            s.style.display = 'none';
            b.insertBefore(s, b.lastChild);
            s.innerHTML = 'Workaround for IE.<s'+'cript></' + 'script>';
            var closure = function() {
                s = s.getElementsByTagName('SCRIPT')[0]; // get with timeout!
                s.language = 'JavaScript';
                if (s.setAttribute) s.setAttribute('src', th.url); else s.src = th.url;
            }
        }
        JsHttpRequest.setTimeout(closure, 10);
        
        // Success.
        return null;
    }
}}
// }}}


// {{{ form
// Loader: FORM & IFRAME.
// [+] Supports file uploading.
// [+] GET and POST methods are supported.
// [+] Supports loading from different domains.
// [-] Uses a lot of system resources.
// [-] Backend data cannot be browser-cached.
// [-] Pollutes browser history on some old browsers.
//
JsHttpRequest.LOADERS.form = { loader: function(req) {
    JsHttpRequest.extend(req._errors, {
        form_el_not_belong:  'Element "%" does not belong to any form!',
        form_el_belong_diff: 'Element "%" belongs to a different form. All elements must belong to the same form!',
        form_el_inv_enctype: 'Attribute "enctype" of the form must be "%" (for IE), "%" given.'
    })
    
    this.load = function() {
        var th = this;
     
        if (!th.method) th.method = 'POST';
        th.url += (th.url.indexOf('?') >= 0? '&' : '?') + 'JsHttpRequest=' + th.id + '-' + 'form';
        
        if (req._reqHeaders.length) return ['no_headers', 'FORM'];

        // If GET, build full URL. Then copy QUERY_STRING to queryText.
        if (th.method == 'GET') {
            if (th.queryText) th.url += (th.url.indexOf('?') >= 0? '&' : '?') + th.queryText;
            if (th.url.length > JsHttpRequest.MAX_URL_LEN) return ['url_too_long', JsHttpRequest.MAX_URL_LEN];
            var p = th.url.split('?', 2);
            th.url = p[0];
            th.queryText = p[1] || '';
        }

        // Check if all form elements belong to same form.
        var form = null;
        var wholeFormSending = false;
        if (th.queryElem.length) {
            if (th.queryElem[0].e.tagName.toUpperCase() == 'FORM') {
                // Whole FORM sending.
                form = th.queryElem[0].e;
                wholeFormSending = true;
                th.queryElem = [];
            } else {
                // If we have at least one form element, we use its FORM as a POST container.
                form = th.queryElem[0].e.form;
                // Validate all the elements.
                for (var i = 0; i < th.queryElem.length; i++) {
                    var e = th.queryElem[i].e;
                    if (!e.form) {
                        return ['form_el_not_belong', e.name];
                    }
                    if (e.form != form) {
                        return ['form_el_belong_diff', e.name];
                    }
                }
            }
            
            // Check enctype of the form.
            if (th.method == 'POST') {
                var need = "multipart/form-data";
                var given = (form.attributes.encType && form.attributes.encType.nodeValue) || (form.attributes.enctype && form.attributes.enctype.value) || form.enctype;
                if (given != need) {
                    return ['form_el_inv_enctype', need, given];
                }
            }
        }

        // Create invisible IFRAME with temporary form (form is used on empty queryElem).
        // We ALWAYS create th IFRAME in the document of the form - for Opera 7.20.
        var d = form && (form.ownerDocument || form.document) || document;
        var ifname = 'jshr_i_' + th.id;
        var s = th.span = d.createElement('DIV');
        s.style.position = 'absolute'; 
        s.style.visibility = 'hidden';
        s.innerHTML = 
            (form? '' : '<form' + (th.method == 'POST'? ' enctype="multipart/form-data" method="post"' : '') + '></form>') + // stupid IE, MUST use innerHTML assignment :-(
            '<iframe name="' + ifname + '" id="' + ifname + '" style="width:0px; height:0px; overflow:hidden; border:none"></iframe>'
        if (!form) {
            form = th.span.firstChild;
        }

        // Insert generated form inside the document.
        // Be careful: don't forget to close FORM container in document body!
        d.body.insertBefore(s, d.body.lastChild);

        // Function to safely set the form attributes. Parameter attr is NOT a hash 
        // but an array, because "for ... in" may badly iterate over derived attributes.
        var setAttributes = function(e, attr) {
            var sv = [];
            var form = e;
            // This strange algorythm is needed, because form may  contain element 
            // with name like 'action'. In IE for such attribute will be returned
            // form element node, not form action. Workaround: copy all attributes
            // to new empty form and work with it, then copy them back. This is
            // THE ONLY working algorythm since a lot of bugs in IE5.0 (e.g. 
            // with e.attributes property: causes IE crash).
            if (e.mergeAttributes) {
                var form = d.createElement('form');
                form.mergeAttributes(e, false);
            }
            for (var i = 0; i < attr.length; i++) {
                var k = attr[i][0], v = attr[i][1];
                // TODO: http://forum.dklab.ru/viewtopic.php?p=129059#129059
                sv[sv.length] = [k, form.getAttribute(k)];
                form.setAttribute(k, v);
            }
            if (e.mergeAttributes) {
                e.mergeAttributes(form, false);
            }
            return sv;
        }

        // Run submit with delay - for old Opera: it needs some time to create IFRAME.
        var closure = function() {
            // Save JsHttpRequest object to new IFRAME.
            top.JsHttpRequestGlobal = JsHttpRequest;
            
            // Disable ALL the form elements.
            var savedNames = [];
            if (!wholeFormSending) {
                for (var i = 0, n = form.elements.length; i < n; i++) {
                    savedNames[i] = form.elements[i].name;
                    form.elements[i].name = '';
                }
            }

            // Insert hidden fields to the form.
            var qt = th.queryText.split('&');
            for (var i = qt.length - 1; i >= 0; i--) {
                var pair = qt[i].split('=', 2);
                var e = d.createElement('INPUT');
                e.type = 'hidden';
                e.name = unescape(pair[0]);
                e.value = pair[1] != null? unescape(pair[1]) : '';
                form.appendChild(e);
            }


            // Change names of along user-passed form elements.
            for (var i = 0; i < th.queryElem.length; i++) {
                th.queryElem[i].e.name = th.queryElem[i].name;
            }

            // Temporary modify form attributes, submit form, restore attributes back.
            var sv = setAttributes(
                form, 
                [
                    ['action',   th.url],
                    ['method',   th.method],
                    ['onsubmit', null],
                    ['target',   ifname]
                ]
            );
            form.submit();
            setAttributes(form, sv);

            // Remove generated temporary hidden elements from the top of the form.
            for (var i = 0; i < qt.length; i++) {
                // Use "form.firstChild.parentNode", not "form", or IE5 crashes!
                form.lastChild.parentNode.removeChild(form.lastChild);
            }
            // Enable all disabled elements back.
            if (!wholeFormSending) {
                for (var i = 0, n = form.elements.length; i < n; i++) {
                    form.elements[i].name = savedNames[i];
                }
            }
        }
        JsHttpRequest.setTimeout(closure, 100);

        // Success.
        return null;
    }    
}}
// }}}