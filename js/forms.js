function showInstituicao() {
    if (document.getElementById('plenaria').checked) {
        document.getElementById('instituicao').style.visibility = 'visible';
        document.getElementById('setorial_area').style.visibility = 'hidden';
        document.getElementById('setorial_area').value = ""
    }
    else {
    	document.getElementById('instituicao').style.visibility = 'hidden';
    	document.getElementById('setorial_area').style.visibility = 'visible';
    	document.getElementById('instituicao').value = ""
	}

}

function showArea() {
    if (document.getElementById('setorial').checked) {
        document.getElementById('setorial_area').style.visibility = 'visible';
        document.getElementById('instituicao').style.visibility = 'hidden';
        document.getElementById('instituicao').value = ""
    }
    else{ 
    	document.getElementById('setorial_area').style.visibility = 'hidden';
    	document.getElementById('instituicao').style.visibility = 'visible';
    	document.getElementById('setorial_area').value = ""
    }

}

function hideAll(){
	document.getElementById('setorial_area').style.visibility = 'hidden';
	document.getElementById('instituicao').style.visibility = 'hidden';
	document.getElementById('setorial_area').value = ""
	document.getElementById('instituicao').value = ""
}


function showEditor(){
	document.getElementById('editor_etapa1').style.visibility = 'visible';
}

function hideEditor(){
	document.getElementById('editor_etapa1').style.visibility = 'hidden';
}