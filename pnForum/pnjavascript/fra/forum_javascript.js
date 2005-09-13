function x () {
    return;
}

function DoSize (fontsize) {
    var revisedMessage;
    var post = document.getElementById("post");
    var currentMessage = post.message.value;
    var sizeBBCode = "[size="+fontsize+"][/size]";
    revisedMessage = currentMessage+sizeBBCode;
    post.message.value=revisedMessage;
    post.message.focus();
    return;
}

function DoColor (fontcolor) {
    var revisedMessage;
    var post = document.getElementById("post");
    var currentMessage = post.message.value;
    var colorBBCode = "[color="+fontcolor+"][/color]";
    revisedMessage = currentMessage+colorBBCode;
    post.message.value=revisedMessage;
    post.message.focus();
    return;
}

function DoPrompt(action) {
    var revisedMessage;
    var post = document.getElementById("post");
    var currentMessage = post.message.value;

    if (action == "url") {
        var thisURL = prompt("URL pour le lien � ajouter", "http://");
        var thisTitle = prompt("Enter the web site title", "Titre de la Page");
        var urlBBCode = "[URL="+thisURL+"]"+thisTitle+"[/URL]";
        revisedMessage = currentMessage+urlBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "email") {
        var thisEmail = prompt("Adresse Email � ajouter", "");
        var emailBBCode = "[EMAIL]"+thisEmail+"[/EMAIL]";
        revisedMessage = currentMessage+emailBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "bold") {
        var thisBold = prompt("Entrez le texte � mettre en gras", "");
        var boldBBCode = "[B]"+thisBold+"[/B]";
        revisedMessage = currentMessage+boldBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "italic") {
        var thisItal = prompt("Entrez le texte � mettre en italique", "");
        var italBBCode = "[I]"+thisItal+"[/I]";
        revisedMessage = currentMessage+italBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "underline") {
        var thisUL = prompt("Entrez le texte � souligner", "");
        var ulBBCode = "[u]"+thisUL+"[/u]";
        revisedMessage = currentMessage+ulBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "image") {
        var thisImage = prompt("Lien URL de l'image � afficher", "http://");
        var imageBBCode = "[IMG]"+thisImage+"[/IMG]";
        revisedMessage = currentMessage+imageBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "quote") {
        var quoteBBCode = "[QUOTE]  [/QUOTE]";
        revisedMessage = currentMessage+quoteBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "code") {
        var codeBBCode = "[CODE]  [/CODE]";
        revisedMessage = currentMessage+codeBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "listopen") {
        var liststartBBCode = "[LIST]";
        revisedMessage = currentMessage+liststartBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "listclose") {
        var listendBBCode = "[/LIST]";
        revisedMessage = currentMessage+listendBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

    if (action == "listitem") {
        var thisItem = prompt("Entrez un nouvel �l�ment de liste. Notez que chaque groupe doit �tre pr�c�d� par une ouverture de liste et suivi d'une fermeture de liste", "");
        var itemBBCode = "[*]"+thisItem;
        revisedMessage = currentMessage+itemBBCode;
        post.message.value=revisedMessage;
        post.message.focus();
        return;
    }

}