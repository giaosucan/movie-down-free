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
					
					if(s['type'] == tFILE)
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
				body='��������������� ��������� ������������. �������� �� �������� ������� �. �������, ������������ ������ ����� ���������. ���������� ��������� ��������� ������������ ��� ������������ ��������, � ��� ������ ����� ������, ��� �. ����� � �. �������. ��������� ������������ �����, �������� �� ������� �����������, ������������.<br><br>\
	������������ ������ �. ���������� ����� �������� ���������������� ����� ��������, ��� ��������� �������� ��������� ���������� � ������. �������������-��������������� ��������� ����������� �������� ���������������� �����������������, ��������� � ����� ������������ �. ������. ����������� ������ ����������� �������������� ��������, ������������� ������������ ���� ��� �. �������� � ����� "�������������� �����". ������������ ������������ �������� ����������������� �������� (�������, ��� ��� �������� ����� ��� ������������ ������������ ��������� � ���������� ��������). ������������ ������ �. ���������� ���������� ����� �������� (������������ �. ����).';
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
	<td width=12><img src="f/i/no.png" width=1 height=1></td><td class="left_menu_head" onmouseover="L._highlight('+i+',\'over\');" onmouseout="L._highlight('+i+',\'out\');" onclick="L._hide('+i+');">'+header+'</td><td width=23 onmouseover="L._highlight('+i+',\'over\');" onmouseout="L._highlight('+i+',\'out\');" onclick="L._hide('+i+');"><img src="f/i/no.png" width=23 height=23 id="i'+i+'" style="background: url(\'f/i/overall.FVER.png\'); background-position: -' + I.coords[up][0] + 'px -' + I.coords[up][1] + 'px;"></td><td width=12><img src="f/i/no.png" width=12 height=1></td>\
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
})());