function send_mesage(warning_message, info_message, error_message){
    // Recuperation des informations du formulaire
    var resa_nb_personnes = $("#nb_personnes").val();
    var resa_date = $("#date").val();
    var resa_heure = $("#heure").val();

    var resa_nom = $("#nom").val();
    var resa_adresse = $("#adresse").val();
    var resa_ville = $("#ville").val();
    var resa_code_postal = $("#code_postal").val();
    var resa_telephone = $("#telephone").val();
    var resa_mail = $("#mail").val();
    var resa_message = $("#message").val();

    var mydate = document.querySelector('[type=date]');
    var day = new Date( mydate.value ).getUTCDay();

    if( day ==  3){
        // Mercredi Impossible
        alert(warning_message);
    } else {
        if( day == 4 || day == 0){
            // Jeudi soir ou Dimanche Soir impossible
            if(resa_heure == "19h45" || resa_heure == "20h00" || resa_heure == "20h15" || resa_heure == "21h00" || resa_heure == "21h15" || resa_heure == "21h30" ){
                alert(warning_message);
            } else {
                // Mercredi midi ou dimanche Midi ok
                if(resa_nom == "" || resa_telephone == "" || resa_mail == ""){
                    alert(error_message);
                } else {
                    // Nettoyage des informations du formulaire
                    $("#nb_personnes").val("");
                    $("#date").val("");
                    $("#heure").val("");
                    $("#nom").val("");
                    $("#adresse").val("");
                    $("#ville").val("");
                    $("#code_postal").val("");
                    $("#telephone").val("");
                    $("#mail").val("");
                    $("#message").val("");

                    // Affichage Message information
                    $("#div_show_info_message").html(info_message + "<img src='pic/wait.gif' />");
                    $("#div_show_info_message").show("slow");

                    _post.resa_nb_personnes = resa_nb_personnes;
                    _post.resa_date = resa_date;
                    _post.resa_heure = resa_heure;
                    _post.resa_nom = resa_nom;
                    _post.resa_adresse = resa_adresse;
                    _post.resa_ville = resa_ville;
                    _post.resa_code_postal = resa_code_postal;
                    _post.resa_telephone = resa_telephone;
                    _post.resa_mail = resa_mail;
                    _post.resa_message = resa_message;
                    _ajax_post('send_message');
                }
            }
        } else {
            // Reste de la semaine ok
            if(resa_nom == "" || resa_telephone == "" || resa_mail == ""){
                alert(error_message);
            } else {
                // Nettoyage des informations du formulaire
                $("#nb_personnes").val("");
                $("#date").val("");
                $("#heure").val("");
                $("#nom").val("");
                $("#adresse").val("");
                $("#ville").val("");
                $("#code_postal").val("");
                $("#telephone").val("");
                $("#mail").val("");
                $("#message").val("");

                // Affichage Message information
                $("#div_show_info_message").html(info_message + "<img src='pic/wait.gif' />");
                $("#div_show_info_message").show("slow");

                _post.resa_nb_personnes = resa_nb_personnes;
                _post.resa_date = resa_date;
                _post.resa_heure = resa_heure;
                _post.resa_nom = resa_nom;
                _post.resa_adresse = resa_adresse;
                _post.resa_ville = resa_ville;
                _post.resa_code_postal = resa_code_postal;
                _post.resa_telephone = resa_telephone;
                _post.resa_mail = resa_mail;
                _post.resa_message = resa_message;
                _ajax_post('send_message');
            }
        }
    }
}

function show_reservation(){
    $('#form_reservation').fadeIn('slow');
    $('.nicescroll').jScrollPane();
}

function close_reservation(){
    $('#form_reservation').fadeOut('slow');
}

function launch_video(){
    $('#video_restaurant').fadeIn('slow');
    var video = document.getElementById("video_restaurant_contenu");
    video.play();
}

function hide_video(){
    $('#video_restaurant').fadeOut('slow');
    var video = document.getElementById("video_restaurant_contenu");
    video.pause();
}

/*function launch_video_mobile(){
    window.open("index.php?to=video_mobile");
}*/

function launch_video_mobile(){
    $('#video_restaurant').fadeIn('slow');
    var video = document.getElementById("video_restaurant_contenu");
    video.play();
}

var meche;

function hide_video_mobile(){
    if (!meche){
        meche = setTimeout(function() {
            if (document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement) {
                // On est en fullscreen => on met en pause ou play
                var video = document.getElementById("video_restaurant_contenu");
                video.paused ? video.play() : video.pause();

            }else{
                $('#video_restaurant').fadeOut('slow');
                var video = document.getElementById("video_restaurant_contenu");
                video.pause();
            }
            meche = false;
        }, 200);
    }
}

function manage_fullscreen_video(){
    if (meche) clearTimeout(meche);
    meche = false;
    var i = document.getElementById("video_restaurant_contenu");

    if (document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement) {
        // On est en fullscreen => on sort
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }else{
        // On passe en fullscreen
        if (i.requestFullscreen) {
            i.requestFullscreen();
        } else if (i.webkitRequestFullscreen) {
            i.webkitRequestFullscreen();
        } else if (i.mozRequestFullScreen) {
            i.mozRequestFullScreen();
        } else if (i.msRequestFullscreen) {
            i.msRequestFullscreen();
        }
    }

}

function show_menu(){
    $('#logo_peron_menu').css('opacity', '0');
    $('#menu').fadeIn('slow');
}

function hide_menu(){
    $('#menu').fadeOut('slow');
    $('#logo_peron_menu').css('opacity', '1');
}

function load_section(section){
    hide_menu();
    window.location.href = '#'+section;
}
