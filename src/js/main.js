//avoir la date du jour en inscription auto
const today = new Date();
const jour = String(today.getDate()).padStart(2,'0');
const mois = String(today.getMonth() + 1).padStart(2,'0');
const annee = today.getFullYear();
const dateFormat = `${jour}/${mois}/${annee}`;


function setDateMaj() {
    document.getElementById('dateMaj').value = dateFormat;
}

//automatisation de l'username
function setUsername() {
    const prenom = document.getElementById('prenom').value.trim().toLowerCase();
    const nom = document.getElementById('nom').value.trim().toLowerCase();
    document.getElementById('username').value = prenom && nom ? `${prenom}.${nom}` : '';
}