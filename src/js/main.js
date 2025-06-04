const today = new Date();
const jour = String(today.getDate()).padStart(2,'0');
const mois = String(today.getMonth() + 1).padStart(2,'0');
const annee = today.getFullYear();
const dateFormat = `${jour}/${mois}/${annee}`;


document.getElementById('dateMaj').value = dateFormat;