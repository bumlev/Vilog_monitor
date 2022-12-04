
<?php

class visitors{
    private $firstname;
    private $lastname;
    private $email;
    private $roleId;
    private $IdNumber;
    private $PhoneNumber;
    private $db;
    private $connected;

    public function __construct($db){
        $this->setdb($db);
    }

    // The Getters

    public function firstname(){ return $this->firstname;}
    public function lastname(){ return $this->lastname;}
    public function email(){ return $this->email;}
    public function roleId(){ return $this->roleId;}
    public function IdNumber(){ return $this->IdNumber;}
    public function PhoneNumber(){ return $this->PhoneNumber ;}
    public function connected(){ return $this->connected ;}

    // The Setters 

    public function setfirstname($firstname){ $this->firstname = $firstname ;}
    public function setlastname($lastname){ $this->lastname = $lastname ;}
    public function setemail($email){ $this->email = $email ;}
    public function setroleId($roleId){ $this->roleId = $roleId ;}
    public function setIdNumber($IdNumber){ $this->IdNumber = $IdNumber ;}
    public function setPhoneNumber($PhoneNumber){ $this->PhoneNumber = $PhoneNumber ;}
    public function setdb(PDO $db){ $this->db = $db; }
    public function setconnected($connected){ $this->connected = $connected; }

    // create a Visitor
    public function createVisitor(){

        $requete = $this->db->prepare('SELECT COUNT(*) as nb FROM  visitors WHERE IdNumber=:idnumb or email=:email or PhoneNumber =:phone AND  PhoneNumber !="" LIMIT 1');
        $requete->execute(array(
            'idnumb'=>$this->IdNumber(),
            'email'=>$this->email(),
            'phone'=>$this->PhoneNumber()
        ));

        $lignes = $requete->fetch();
        $row = $lignes['nb'];

        if($row == 0)
        {
            try{
                $requete = $this->db->prepare('INSERT INTO visitors (firstname , lastname , email , roleId , IdNumber , PhoneNumber , connected) VALUES(:firstname , :lastname , :email , :roleId , :IdNumber , :PhoneNumber , :connected)');
                $requete->execute(array(
                    'firstname'=>$this->firstname(),
                    'lastname'=>$this->lastname(),
                    'email'=>$this->email(),
                    'roleId'=>$this->roleId(),
                    'IdNumber'=>$this->IdNumber(),
                    'PhoneNumber'=>$this->PhoneNumber(),
                    'connected'=>$this->connected()
                ));
                echo json_encode(true);
            }catch(Exception $e){
                die('Erreur :' .$e->getMessage());
            }
        }else{
            echo json_encode(false);
        }
    }

    // search a visitor
    public function searchVisitor(){
        $requete = $this->db->prepare('SELECT * FROM visitors as Visitors
        LEFT JOIN roles as Roles 
        ON Visitors.roleId = Roles.id
        WHERE IdNumber=:idnumber LIMIT 1');

        $requete->execute(array(
            'idnumber'=>$this->IdNumber()
        ));

        $donnees = $requete->fetch();
        echo json_encode($donnees);
    }

    // Update a visitor
    public function updateVisitor($id){
        $requete= $this->db->prepare('UPDATE visitors set firstname=:firstname , lastname=:lastname , email=:email , roleId=:roleId , IdNumber=:idnumb , PhoneNumber=:phone , connected=:connected
         WHERE id=:id');

        $requete->execute(array(
            'firstname'=>$this->firstname(),
            'lastname'=>$this->lastname(),
            'email'=>$this->email(),
            'roleId'=>$this->roleId(),
            'idnumb'=>$this->IdNumber(),
            'phone'=>$this->PhoneNumber(),
            'connected'=>$this->connected(),
            'id'=>$id
        ));
    }

    public function disconnect($id){
        $requete = $this->db->prepare('UPDATE visitors set connected=:connected WHERE id=:id');
        $requete->execute(array(
            'connected'=>$this->connected(),
            'id'=>$id
        ));
    }
}
?>

<?php 
    include_once 'objet.php';
    $visitors = new visitors($db);
    
    $error = array();
    $Error = array();

    if(isset($_POST['firstname']) && !empty($_POST['firstname']) ){
        $visitors->setfirstname($_POST['firstname']);
    }elseif (isset($_POST['firstname']) && empty($_POST['firstname'])) {
        $error['firstname'] = "firstname is empty !";
    }

    if(isset($_POST['lastname']) && !empty($_POST['lastname'])){
        $visitors->setlastname($_POST['lastname']);
    }elseif (isset($_POST['lastname']) && empty($_POST['lastname'])) {
        $error +=['lastname' => "lastname is empty !"];
    }

    if(isset($_POST['email']) && !empty($_POST['email'])){
        $visitors->setemail($_POST['email']);
    }elseif (isset($_POST['email']) && empty($_POST['email'])) {
        $error +=['email' => "email is empty !"];
    }

    if(isset($_POST['role']) && !empty($_POST['role'])){
        $visitors->setroleId($_POST['role']);
    }elseif (isset($_POST['role']) && empty($_POST['role'])) {
        $error +=['role' => "role is empty !"];
    }

    if(isset($_POST['IdNumber']) && !empty($_POST['IdNumber'])){
        $visitors->setIdNumber($_POST['IdNumber']);
    }elseif (isset($_POST['IdNumber']) && empty($_POST['IdNumber'])) {
        $error +=['IdNumber' => "IdNumber is empty !"];
    }

    if(isset($_POST['phone'])){
        $visitors->setPhoneNumber($_POST['phone']);
    }
  
    if(empty($error) && isset($_POST['register'])){
        $visitors->setconnected(1);
        $visitors->createVisitor();    
    }elseif(empty($error) && isset($_POST['search'])){
        $visitors->searchVisitor();
    }elseif(empty($error) && isset($_POST['present'])){
        $visitors->setconnected(1);
        $visitors->updateVisitor($_POST['visitor']);
    }elseif(empty($error) && isset($_POST['disconnect'])){
        $visitors->setconnected(0);
        $visitors->disconnect($_POST['visitor']);
    }   
    elseif(!empty($error)){
        $Error['error'] = $error;
        echo json_encode($Error);
    }
?>