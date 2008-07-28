/*
  ==============================================================================================
  PHPAskIt 3.1 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

/* ---------------------------------------------------------------------------------------------
			HUUUUUGE THANKS TO JENNY OF PRISM-PERFECT.NET FOR ALL OF THIS PAGE!
------------------------------------------------------------------------------------------------
																								*/

// AJAX FOR QUESTION AND ANSWER PROCESSING

// ##### ANSWERS #####
addEvent(window, 'load', function() {
	var answers = getElementsByClass('answer', null, 'p');
	var qs = getElementsByClass('question', null, 'p');
	for (i = 0; i < answers.length; i++) {
		addEvent(answers[i], 'click', getAnswerForm);
	}
	for (i = 0; i < qs.length; i++) {
		addEvent(qs[i], 'click', getQuestionForm);
	}

});
function getAnswerForm() {
	el = getEventSource(arguments[0]);
	qID = el.id.replace('answer', '');

	var url = 'admin.php?edit=answer&inline=true&qu=' + qID;
	var targetID = 'answer' + qID;
	makeRequest(url, targetID, 'GET', null, qID);
	document.getElementById(targetID).className = 'answer active';
	removeEvent(el, 'click', getAnswerForm);

	return false;
}
function submitAnswer(f) {
	var qs = 'token=' + encodeURIComponent(f.token.value) + '&id=' + encodeURIComponent(f.id.value) + '&answer=' + encodeURIComponent(f.answer.value);
	var targetID = 'answer' + f.id.value;
	makeRequest('admin.php?edit=answer&inline=true', targetID, 'POST', qs, f.id.value);
	addEvent(document.getElementById(targetID), 'click', getAnswerForm);

	return false;
}

// ###### QUESTIONS ######
function getQuestionForm() {
	el = getEventSource(arguments[0]);
	qID = el.id.replace('question', '');

	var url = 'admin.php?edit=question&inline=true&qu=' + qID;
	var targetID = 'question' + qID;
	makeRequest(url, targetID, 'GET', null, qID);
	document.getElementById(targetID).className = 'question active';
	removeEvent(el, 'click', getQuestionForm);

	return false;
}
function submitQuestion(f) {
	var qs = 'token=' + encodeURIComponent(f.token.value) + '&id=' + encodeURIComponent(f.id.value) + '&question=' + encodeURIComponent(f.question.value);
	var targetID = 'question' + f.id.value;
	makeRequest('admin.php?edit=question&inline=true', targetID, 'POST', qs, f.id.value);
	addEvent(document.getElementById(targetID), 'click', getQuestionForm);

	return false;
}

// ###### GENERAL #####
function getEventSource(e) {
	if (typeof e == 'undefined') var e = window.event;

	if (typeof e.target != 'undefined') return e.target;
	else if (typeof e.srcElement != 'undefined') return e.srcElement;
	else if (typeof e == 'object') return e;
	else if (typeof e == 'string') return document.getElementById(e);
	else return false;
}
function getElementsByClass(searchClass, node, tag) {
	var classElements = new Array();

	if (node == null) node = document;
	if (tag == null) tag = '*';

	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\s)' + searchClass + '(\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if (pattern.test(els[i].className)) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}
function addEvent(obj, type, fn) {
	if (obj.attachEvent) {
		obj['e' + type + fn] = fn;
		obj[type + fn] = function(){obj['e' + type + fn](window.event);}
		obj.attachEvent('on' + type, obj[type + fn]);
	}
	else obj.addEventListener(type, fn, false);
}
function removeEvent(obj, type, fn) {
	if (obj.detachEvent) {
		obj.detachEvent('on' + type, obj[type + fn]);
		obj[type + fn] = null;
	}
	else obj.removeEventListener(type, fn, false);
}
function getXmlHttpObject() {
	var xmlHttp = false;

	try {
		xmlHttp = new XMLHttpRequest();
	}
	catch (trymicrosoft) {
		try {
			xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
		}
		catch (othermicrosoft) {
			try {
				xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
			}
			catch (failed) {
				xmlHttp = false;
			}
		}
	}

	return xmlHttp;
}
function makeRequest(url, ID, method, qs, origid) {
	var xmlHttp = getXmlHttpObject();

	xmlHttp.onreadystatechange = function() {
		if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
			el = document.getElementById(ID);
			el.innerHTML = xmlHttp.responseText;
			if (qs != null && url == 'admin.php?edit=answer&inline=true' && xmlHttp.responseText != '') el.className = 'answer';
			else if (qs != null && url == 'admin.php?edit=answer&inline=true' && (xmlHttp.responseText == '' || xmlHttp.responseText == null)) el.className = 'answer unanswered';
			else if (qs != null && url == 'admin.php?edit=question&inline=true') el.className = 'question';
			document.getElementById('indicator' + origid).style.display = 'none';
		}
	}

	xmlHttp.open(method, url, true);
	document.getElementById('indicator' + origid).style.display = 'inline';
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	xmlHttp.setRequestHeader('Content-Type', contentType);

	xmlHttp.send(qs);
}