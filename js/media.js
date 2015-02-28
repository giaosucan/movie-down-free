function alertBrokenLink(id) {
	if (confirm("Report this movie was broken ?")) {
		try{
			http.open('POST',  'index.php');
			http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			http.onreadystatechange = BrokenResponse;
			http.send('url=Broken,'+id);
		}
		catch(e){}
		finally{}
	}
}

function BrokenResponse() {
	try {
		if((http.readyState == 4)&&(http.status == 200)){
			response = http.responseText;
			if (response == 1) alert("Your report has been sent. Thank for your reporting.");
			else alert("Error. Try again later.");
		}
  	}
	catch(e){
		alert("Error. Try again later.");
	}
	finally{}
}

function do_search() {
	kw = document.getElementById("keyword").value;
	if (!kw) alert('Please enter keyword');
	else {
		kw = encodeURIComponent(kw);
		s_type = document.getElementById("searchType");
		type = s_type.options[s_type.selectedIndex].value;
		switch (type) {
			case 'title' : type = 1; break;
			case 'option' : type = 2; break;
			case 'collect' : type = 3; break;
		}
		last_url = '';
		window.location.href = 'Search,'+type+','+kw;
	}
	return false;

}