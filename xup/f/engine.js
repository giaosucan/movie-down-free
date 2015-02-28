var Engine = (E = new (function(){
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
		if(_items[item]['type']!=tFILE&&_items[item]['type']!=tDIR) return draw_menu_for_item_callback(item);
		
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
		
		if(i['type']==tFILE||i['type']==tDIR) 
		{
			T.cancel_draw();
			
			var dr = function(){
				if(i['type']==tDIR) info['size']=i['size'];
				L.draw({0: 'operations',1: info });
				
				I.change_status([['Name',info['filename']],['Type',info['type']],['Size',info['size']]]);
			}
			
			if(!info) D.qr('index.php?act=info', i, function(d,err) { info = d; dr(); });
			else dr();

		}/*else if(i['type']==tDIR)
		{
			L.draw({0: 'operations',1: {name: 'details', filename: i['name'], dir: true, size: i['size']}});
		}*/else if(i['type']==tDRIVE)
		{	
			L.draw({0: 'common', 1: { name: 'details', filename: i['name'], dir: false, type: i['descr'], free: i['free'], total: i['total'], fs: i['fs'] }});
		}
	}
	
	// function that deletes the selected item (for ex. file or folder).
	
	T.delete_item = function()
	{
		var i = R.get_selected_items()[0];
		if(i['type']!=tDIR && i['type']!=tFILE || !confirm('Do you really want to delete that ' + (i['type']==tFILE ? 'file' : 'folder') + '?')) return; // you can only delete files and folders
		
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
			if(items[i]['type']!=tDIR && items[i]['type']!=tFILE) return; // you can only delete files and folders
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
		if(i['type']!=tDIR && i['type']!=tFILE) return; // you can only rename files and folders
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
			if(!res) res = {state: FINISHED_COPY};
			if(res['state'] == FINISHED_COPY)
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
				var recursive = i['type'] == tDIR ? confirm('CHMOD items recursively (chmod also subdirectories and files in subdirectories)?') : false;
				
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
				if(confirm('Auto-update failed.\nDo you want to use advanced way to update SNAME (version will be changed to light)?')) window.location='index.php?version=light&DIR=.&act=download-new';
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
