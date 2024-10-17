<?php
require_once 'fct.inc.php';
/** 
 * Classe d'accÃ¨s aux donnÃ©es. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{   		
      	private static $serveur='mysql:host=localhost';
      	private static $bdd='dbname=gsbextranet';   		
      	private static $user='gsbextranetAdmin' ;    		
      	private static $mdp='Lookie62400' ;	
	private static $monPdo;
	private static $monPdoGsb=null;
		
/**
 * Constructeur privÃ©, crÃ©e l'instance de PDO qui sera sollicitÃ©e
 * pour toutes les mÃ©thodes de la classe
 */				
	private function __construct(){
          
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crÃ©e l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return 'l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * vÃ©rifie si le login et le mot de passe sont corrects
 * renvoie true si les 2 sont corrects
 * @param type $lePDO
 * @param type $login
 * @param type $pwd
 * @return bool
 * @throws Exception
 */
function checkUser($login,$pwd):bool {
    //AJOUTER TEST SUR TOKEN POUR ACTIVATION DU COMPTE
    $user=false;
    $pdo = PdoGsb::$monPdo;
    $monObjPdoStatement=$pdo->prepare("SELECT motDePasse
    FROM medecin WHERE mail= :login AND token IS NULL");
    $bvc1=$monObjPdoStatement->bindValue(':login',$login,PDO::PARAM_STR);
    if ($monObjPdoStatement->execute()) {
        $unUser=$monObjPdoStatement->fetch();
        if (is_array($unUser)) {
            if (password_verify($pwd, $unUser['motDePasse'])) {
                $user = true;
            }
        }
    }
    else
        throw new Exception("erreur dans la requÃªte");
return $user;   
}


	

function donneLeMedecinByMail($login) {
    
    $pdo = PdoGsb::$monPdo;
    $monObjPdoStatement=$pdo->prepare("SELECT id, nom, prenom,mail FROM medecin WHERE mail= :login");
    $bvc1=$monObjPdoStatement->bindValue(':login',$login,PDO::PARAM_STR);
    if ($monObjPdoStatement->execute()) {
        $unUser=$monObjPdoStatement->fetch();
       
    }
    else
        throw new Exception("erreur dans la requÃªte");
return $unUser;   
}


public function tailleChampsMail(){
    

    
     $pdoStatement = PdoGsb::$monPdo->prepare("SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = 'medecin' AND COLUMN_NAME = 'mail'");
    $execution = $pdoStatement->execute();
$leResultat = $pdoStatement->fetch();
      
      return $leResultat[0];
    
       
       
}


public function creeMedecin($email, $mdp, $nom, $prenom)
{
    $mdpverif = password_hash($mdp, PASSWORD_DEFAULT);
    
    $pdoStatement = PdoGsb::$monPdo->prepare("INSERT INTO medecin(id,nom,prenom,mail, motDePasse,dateCreation,dateConsentement) "
            . "VALUES (null,:leNom, :lePrenom, :leMail, :leMdp, now(),now())");
            $bv4 = $pdoStatement->bindValue(':leNom', $nom); 
    $bv3 = $pdoStatement->bindValue(':lePrenom', $prenom);
    $bv1 = $pdoStatement->bindValue(':leMail', $email);
    $bv2 = $pdoStatement->bindValue(':leMdp', $mdpverif); 
    $execution = $pdoStatement->execute();
    return $execution;
}


function testMail($email){
    $pdo = PdoGsb::$monPdo;
    $pdoStatement = $pdo->prepare("SELECT count(*) as nbMail FROM medecin WHERE mail = :leMail");
    $bv1 = $pdoStatement->bindValue(':leMail', $email);
    $execution = $pdoStatement->execute();
    $resultatRequete = $pdoStatement->fetch();
    if ($resultatRequete['nbMail']==0)
        $mailTrouve = false;
    else
        $mailTrouve=true;
    
    return $mailTrouve;
}

function donneMedecin($id) {
    $pdo = PdoGsb::$monPdo;
    $monObjPdoStatement = $pdo->prepare("SELECT nom, prenom, mail, dateCreation, rpps, dateConsentement, anneeNaissance, anneeDiplome, Telephone
                                         FROM medecin WHERE id = :id");
    $monObjPdoStatement->bindValue(':id', $id, PDO::PARAM_INT);
    if ($monObjPdoStatement->execute()) {
        $unUser = $monObjPdoStatement->fetch(PDO::FETCH_ASSOC); 
        if ($unUser !== false) {
            return $unUser;  
        } else {
            throw new Exception("Aucun médecin trouvé");
        }
    } else {
        throw new Exception("Erreur");
    }
}


        
 
function connexionInitiale($mail){
     $pdo = PdoGsb::$monPdo;
    $medecin= $this->donneLeMedecinByMail($mail);
    $id = $medecin['id'];
    $this->ajouteConnexion($id);
    
}

function ajouteConnexion($id) {
    $pdoStatement = PdoGsb::$monPdo->prepare("INSERT INTO historiqueconnexion (idMedecin, dateConnexion) VALUES (:idMedecin, NOW())");
    $pdoStatement->bindValue(':idMedecin', $id, PDO::PARAM_INT);
    if ($pdoStatement->execute()) {
        return true;  
    } else {
        throw new Exception("Erreur lors de l'ajout de la connexion.");
    }
}

function donneInfoPortabilite($id){
    $pdo = PdoGsb::$monPdo;
           $monObjPdoStatement=$pdo->prepare("SELECT id,nom,prenom FROM medecin WHERE id= :lId");
    $bvc1=$monObjPdoStatement->bindValue(':lId',$id,PDO::PARAM_INT);
    if ($monObjPdoStatement->execute()) {
        $unUser=$monObjPdoStatement->fetch();
   
    }
    else
        throw new Exception("erreur");
           
    

}

function ajouteCodeBDD($id_medecin) {
    $code = generateCode();
    $pdoStatement = PdoGsb::$monPdo->prepare("INSERT INTO authentification (code, id_medecin, temps_co) 
    VALUES (:code, :id_medecin, NOW())");
    $pdoStatement->bindValue(':code', $code, PDO::PARAM_STR);
    $pdoStatement->bindValue(':id_medecin', $id_medecin, PDO::PARAM_INT);
    if ($pdoStatement->execute()) {
        return true;  
    } else {
        throw new Exception("Erreur lors de l'ajout de la connexion avec code.");
    }
}

function donneinfosmedecin($id){
  
       $pdo = PdoGsb::$monPdo;
           $monObjPdoStatement=$pdo->prepare("SELECT id,nom,prenom FROM medecin WHERE id= :lId");
    $bvc1=$monObjPdoStatement->bindValue(':lId',$id,PDO::PARAM_INT);
    if ($monObjPdoStatement->execute()) {
        $unUser=$monObjPdoStatement->fetch();
   
    }
    else
        throw new Exception("erreur");   
}


}
?>