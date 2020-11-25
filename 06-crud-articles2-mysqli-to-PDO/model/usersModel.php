<?php
// connect function
function connectUser($connect,$login,$pwd){
    // traitement des données
    $login = htmlspecialchars(strip_tags(trim($login)),ENT_QUOTES);
    $pwd = htmlspecialchars(strip_tags(trim($pwd)),ENT_QUOTES);
    // request
    $sql = "SELECT u.idusers, u.thename, d.iddroit, d.droit_name
	FROM users u
    INNER JOIN droit d 
		ON d.iddroit = u.droit_iddroit
    WHERE u.thename='$login' AND u.thepwd='$pwd';";
    $recup = $connect->query($sql) or die(mysqli_error($connect));

    if($recup->rowCount()){
        return $recup->fetch(PDO::FETCH_ASSOC);
    }else{
        return false;
    }

}

// find all user (Rédacteur and administateur)
function AllUser($c){
    $sql="SELECT idusers, thename FROM users ORDER BY thename ASC;";
    $request = $c->query($sql);
    return $request->fetchAll(PDO::FETCH_ASSOC);
}