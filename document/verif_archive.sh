#!/bin/bash

if [ $0 = "bash" ]
then
    echo "[ERR]  ne lancez pas le script avec \"source\""
    echo "       lancez-le directement comme une commande"
    return
fi


function souligner
{
    phrase=$2
    echo $2
    #printf "$1"'%.0s' `seq 1 ${#phrase}`
    #echo
    seq -s$1 0 ${#phrase} | tr -d '[0-9]'
}


#
# nombre d'arguments
#
if [ $# -eq 0 -o $# -gt 2 ]
then
    echo "[ERR]  nombre d'arguments incorrect"
    echo "       usage : " $0 "nom1 [nom2]"
    exit
fi

#
# ordre lexicographique pour les noms des auteurs (si binôme)
#
if [ $# -eq 2 ]
then
    if [[ "$1" > "$2" ]]
    then
        echo "[ERR]  les noms ne sont pas dans l'ordre alphabétique"
        exit
    fi
    filename=$1_$2_projet.tar.gz
else
    filename=$1_projet.tar.gz
fi


echo
souligner '=' "Analyse du fichier \"$filename\""


#
# existence du fichier
#
if [ ! -f $filename ]
then
    echo "[ERR]  fichier inexistant"
    exit
else
    echo "[ok]   fichier présent"
fi

#
# accès en lecture
#
if [ ! -r $filename ]
then
    echo "[ERR]  fichier non en lecture"
    exit
else
    echo "[ok]   fichier avec accès en lecture"
fi

#
# taille correcte ?
#
taille=`stat -c %s $filename`
if [ $taille -eq 0 ]
then
    echo "[ERR]  l'archive est vide"
    exit
else
    echo "[ok]   fichier non vide"
fi

if [ $taille -lt 10240 ]
then
    echo "[Warn] l'archive fait moins de 10 Ko, est-ce normal ?"
fi
if [ $taille -gt 10485760 ]
then
    echo "[Warn] l'archive dépasse 10 Mo, n'avez-vous pas laissé des fichiers inutiles ?"
fi

#
# compressé avec gzip ?
#
type=`file -b $filename | cut -c -20`
if [[ $type != "gzip compressed data" ]]
then
    echo "[ERR]  le fichier n'a pas le bon type"
    echo "       voici le type de l'objet :"
    echo -n "       "
    file $filename
    exit
else
    echo "[ok]   compression fichier (gzip)"
fi

#
# est-ce bien une archive .tar ?
#
tar ztf $filename 2> /dev/null > /dev/null
ok=$?
if [ $ok -ne 0 ]
then
    echo "[ERR]  lecture archive"
    exit
else
    echo "[ok]   lecture archive"
fi


echo
souligner '=' "Analyse de l'arborescence de l'archive"


#
# répertoire PROJET ?
#
reponse=`tar zvtf $filename --no-recursion PROJET 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas d'entrée PROJET dans l'archive"
    exit
fi

if [ `echo $reponse | cut -c -1` != 'd' ]
then
    echo "[ERR]  l'entrée PROJET n'est pas un répertoire"
    exit
else
    echo "[ok]   répertoire PROJET"
fi

#
# rapport
#
reponse=`tar zvtf $filename --no-recursion PROJET/rapport.pdf 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas de fichier PROJET/rapport.pdf dans l'archive"
    exit
else
    echo "[ok]   fichier PROJET/rapport.pdf"
fi

#
# base de donnees
#
reponse=`tar zvtf $filename --no-recursion PROJET/bd.sql 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas de fichier PROJET/bd.sql dans l'archive"
    exit
else
    echo "[ok]   fichier PROJET/bd.sql"
fi

#
# liste fichiers
#
reponse=`tar zvtf $filename --no-recursion PROJET/ls-R.txt 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas de fichier PROJET/ls-R.txt dans l'archive"
    exit
else
    echo "[ok]   fichier PROJET/ls-R.txt"
fi

#
# site
#
reponse=`tar zvtf $filename --no-recursion PROJET/site 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas d'entrée PROJET/site dans l'archive"
    exit
else
    echo "[ok]   entrée PROJET/site"
fi

if [ `echo $reponse | cut -c -1` != 'd' ]
then
    echo "[ERR]  l'entrée PROJET/site n'est pas un répertoire"
    exit
else
    echo "[ok]   répertoire PROJET/site"
fi


#
# répertoires classiques
#
reps="config public dbvente src templates"
for rep in $reps
do
    reponse=`tar zvtf $filename --no-recursion PROJET/site/$rep 2> /dev/null`
    if [ ! "$reponse" ]
    then
        echo "[ERR]  pas d'entrée PROJET/site/$rep dans l'archive"
        exit
    else
        echo "[ok]   entrée PROJET/site/$rep"
    fi

    if [ `echo $reponse | cut -c -1` != 'd' ]
    then
        echo "[ERR]  l'entrée PROJET/site/$rep n'est pas un répertoire"
        exit
    else
        echo "[ok]   répertoire PROJET/site/$rep"
    fi
done


#
# répertoires .git à part
#
reps=".git"
for rep in $reps
do
    reponse=`tar zvtf $filename --no-recursion PROJET/site/$rep 2> /dev/null`
    if [ ! "$reponse" ]
    then
        echo "[Warn] (erreur en fait) pas d'entrée PROJET/site/$rep dans l'archive"
    else
        echo "[ok]   entrée PROJET/site/$rep"
        if [ `echo $reponse | cut -c -1` != 'd' ]
        then
            echo "[Warn] (erreur en fait) l'entrée PROJET/site/$rep n'est pas un répertoire"
        else
            echo "[ok]   répertoire PROJET/site/$rep"
        fi
    fi

done

#
# répertoires classiques devant être absents
#
reps="var vendor"
for rep in $reps
do
    reponse=`tar zvtf $filename --no-recursion PROJET/site/$rep 2> /dev/null`
    if [ "$reponse" ]
    then
        echo "[ERR]  l'entrée PROJET/site/$rep ne devrait pas être dans l'archive"
        exit
    else
        echo "[ok]   entrée PROJET/site/$rep est bien absente"
    fi
done


#
# fichiers classiques
#
fics=".env composer.json composer.lock symfony.lock"
for fic in $fics
do
    reponse=`tar zvtf $filename --no-recursion PROJET/site/$fic 2> /dev/null`
    if [ ! "$reponse" ]
    then
        echo "[ERR]  pas d'entrée PROJET/site/$fic dans l'archive"
        exit
    else
        echo "[ok]   entrée PROJET/site/$fic"
    fi

    if [ `echo $reponse | cut -c -1` != '-' ]
    then
        echo "[ERR]  l'entrée PROJET/site/$fic n'est pas un fichier régulier"
        exit
    else
        echo "[ok]   fichier PROJET/site/$fic"
    fi
done

#
# base de données
#
reponse=`tar zvtf $filename --no-recursion --wildcards 'PROJET/site/dbvente/*.db' 2> /dev/null`
if [ ! "$reponse" ]
then
    echo "[ERR]  pas de fichier PROJET/site/dbvente/*.db dans l'archive"
    exit
else
    echo "[ok]   fichier PROJET/site/sqlite/*.db"
fi


echo
souligner "-" "Analyse effectuée avec succès"
