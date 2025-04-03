$('#swalCorresp').dblclick(function(){
    let titre = getElementById(titre);

        Swal.fire({
            titre: "Détails",
            html:
            <p><strong> Titre:</strong> ${titre} </p>
            ,
            icon: "info",
            confirmButtonText: "Ok"
            });
})