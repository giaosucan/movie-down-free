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
		
		/*var cols = '<th id="tv_name" width="' + w['name'] + '" onmouseover="R._tv_h(this,\'over\');" onmouseout="R._tv_h(this,\'out\')" onmousedown="R._tv_h(this,\'down\');"><img src="f/i/no.png" width="'+ c['tv_lsep'][2] +'" height="'+ c['tv_lsep'][3] +'" style="background: url(\'f/i/overall.FVER.png\'); background-position: -'+c['tv_lsep'][0]+'px -'+c['tv_lsep'][1]+'px;" border=0 align="absmiddle">&nbsp;Name</th>';*/
		
		T.un_cl();
		E.set_filtered_items(items);
		
		begin = '<table width="100%" cellspacing=0 cellpadding=0 border=0>'+/*<thead><tr class="upper" height="20">' + cols + '<th class="bg"><img src="f/i/no.png" width="'+ c['tv_sep'][2] +'" height="'+ c['tv_sep'][3] +'" style="background: url(\'f/i/overall.FVER.png\'); background-position: -'+c['tv_sep'][0]+'px -'+c['tv_sep'][1]+'px; position: relative; left: -10px;" border=0 align="absmiddle">&nbsp;</th></thead></tr>*/'<tbody>';
		
		for(var k in items)
		{
			var i = items[k];
			var _k = (i.k ? i.k : k); // the global number (in _items), required for the filter usage
			
			var dbl = (i['type']==tDIR || i['type']==tDRIVE ? 'E.go2(E.path(' + _k + '));' : 'E.edit_file_for_item(' + _k + ');' );
			
			var click = 'R.cl(this,event);';
			
			tmp[k] = '<tr><td style="overflow: hidden;"><div class="d16"><span id="it' + _k + '" class="item16" onmousedown="if(!R.is_inp(event)){' + click + ';return false;}" ondblclick="if(!R.is_inp(event)) {' + dbl + '; return false;};" onmouseover="R.handle_over(event,this);" onmouseout="R.handle_out(event,this);"><img src="f/iconz/16-' + i['icon'] + '.png" width=16 height=16 border=0 class="i16" align="absmiddle">' + i['name'] + '</span></div></td><td>&nbsp;</td></tr>';
		}
		
		end = '</tbody></table>';
		
		if(tmp.length==0)
		{
			begin=(end='');
			tmp = [_msg((_filter.length>0 ? 'Result of filtering is empty.' : 'Directory is empty'))];
		}
		if(tmp.length>JS_MAX_ITEMS && !force)
		{
			_simple_view = true;
			
			begin+='<tr><td><div align="center" style="color: red; padding: 10px;"><b>it make take a while to display all files in normal mode.<br><a href="#" onclick="R.draw(E.get_filtered_items(),true);return false;">click here to render in normal mode anyway</a></b></div><pre style="padding: 10px; padding-top: 0px;">';
			for(var k in items)
			{
				var i = items[k];
				
				if(i['type']==tDIR || i['type']==tDRIVE) tmp[k] = '<a href="#" onclick="E.go2(E.path(' + (i.k ? i.k : k) + '));">' + i['name'] + '</a>/\n';
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

var Render = (R = new Render_Views['table']());