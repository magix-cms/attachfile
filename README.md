# Dailymotion
Plugin Attach File in product for Magix CMS 3

Ajoute des fichiers aux produits sur votre site.

## Installation
 * Décompresser l'archive dans le dossier "plugins" de magix cms
 * Connectez-vous dans l'administration de votre site internet
 * Cliquer sur l'onglet plugins du menu déroulant pour sélectionner thematic.
 * Une fois dans le plugin, laisser faire l'auto installation
 * Il ne reste que la configuration du plugin pour correspondre avec vos données.
 * Copier le contenu du dossier skin/public dans le dossier de votre skin.

## Afficher les vidéos dans le produit
Ajouter la ligne suivante dans le tpl du produit où vous souhaitez afficher les vidéos
````smarty
<div class="attach-file">
    {include file="attachfile/brick/files.tpl"}
</div>
````