function getCookie(cname) {
	var out = '';
	if (document.cookie.length > 0) {
		var cstart = document.cookie.indexOf(cname + '='); /* this is a comment */
		if (cstart != -1) {
			cstart = cstart + cname.length + 1;
			var cend = document.cookie.indexOf(';', cstart);
			if (cend == -1) {
				cend = document.cookie.length;
			}
			var s = document.cookie.substring(cstart, cend);
			out = decodeURIComponent(s); // this is another comment
		}
	}
	return out;
}

function setCookie(cname, value, expiredays) {
	var exdate = new Date();
	var d = exdate.getDate();
	exdate.setDate(d + expiredays);
	var ed = '';
	if (expiredays > 0) {
		ed = '; expires=' + exdate.toUTCString();
	}
	document.cookie = cname + '=' + encodeURIComponent(value) + ed;
}

function checkCookie() {
	var un = getCookie('username');
	if (un.length > 0) {
		alert('Welcome again ' + un + '!');
	} else {
		var un2 = prompt('Please enter your name:', '');
		var oneyear = 365;
		if (un2.length > 0) {
			setCookie('username', un2, oneyear);
		}
	}
}
