/*
* Par Iksaif
*/


/* Ou un lien dans la fenêtre qui a ouvert la page courante .. ouvre un lien dans la même fenêtre si c'est pas possible */
function goOpener(url)
{
	if(window.opener)
	{
		window.opener.location.href = url;
		window.close();
	} else {
		window.location.href= url;
	}
	return false;
}


/*
* Récuperer l'objet xmlhttp
*/
function getHTTPObject()
{
	var xmlhttp = false;

	/* Compilation conditionnelle d'IE */
	/*@cc_on
	@if (@_jscript_version >= 5)
		try
		{
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (E)
			{
			xmlhttp = false;
			}
		}
	@else
		xmlhttp = false;
	@end @*/

	/* on essaie de creer l'objet si ce n'est pas deja fait */
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
	{
		try
		{
			xmlhttp = new XMLHttpRequest();
		}
		catch (e)
		{
			xmlhttp = false;
		}
	}
	return xmlhttp;
}

/*
* Faire une requette a partir de machins
*/
function ajaxRequest(xmlhttp, method, url, data, callback)
{
	if (!xmlhttp)
		return false;

	xmlhttp.onreadystatechange = callback;

	if(method == "GET")
	{
		if(data == 'null')
		{
			xmlhttp.open("GET", url, true); //ouverture asynchrone
		}
		else
		{
			xmlhttp.open("GET", url+"?"+data, true);
		}
		xmlhttp.send(null);
	}
	else if(method == "POST")
	{
		xmlhttp.open("POST", url, true); //ouverture asynchrone
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xmlhttp.send(data);
	}
	return true;
}

function loadHtml2Canvas() {
	return new Promise((resolve, reject) => {
		if (window.html2canvas) {
			resolve(window.html2canvas);
		} else {
			const script = document.createElement('script');
			script.src = "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
			script.onload = () => resolve(window.html2canvas);
			script.onerror = () => reject(new Error("Erreur lors du chargement de html2canvas."));
			document.body.appendChild(script);
		}
	});
}

function screenShot(idToCapture, event, button) {
	event.preventDefault();
	loadHtml2Canvas().then(html2canvas => {
		const captureZone = document.getElementById(idToCapture);
		let bgColor = window.getComputedStyle(document.getElementById('module')).backgroundColor;
		if(!bgColor || bgColor === "transparent" || bgColor === "rgba(0, 0, 0, 0)") {
			bgColor = window.getComputedStyle(document.getElementById('contenu')).backgroundColor;
		}
		html2canvas(captureZone,{
			allowTaint: true,
			useCORS: true,
			backgroundColor: bgColor 
		}).then(canvas => {
			canvas.toBlob(blob => {
				if (blob) {
					navigator.clipboard.write([
						new ClipboardItem({ "image/png": blob })
					]).then(() => {
						button.textContent += ' \u2713';
					}).catch(err => {
						console.error("Erreur lors de la copie :", err);
					});
				}
			});
		});
	}).catch(err => {
		console.error("Erreur lors du chargement de html2canvas :", err);
	});
}

function copyToClipboard(text) {
	return new Promise((resolve, reject) => {
		if (navigator.clipboard) {
			navigator.clipboard.writeText(text).then(() => {
				resolve(true);
			}).catch(err => {
				reject(false);
			});
		} else {
			reject(false);
		}
	});
  }