<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class visitors{
    private $firstname;
    private $lastname;
    private $email;
    private $roleId;
    private $IdNumber;
    private $PhoneNumber;
    private $db;
    private $connected;
    private $password;
    private $authenticated;
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
    public function password(){ return $this->password ;}
    public function authenticated(){ return $this->authenticated ;}

    // The Setters 

    public function setfirstname($firstname){ $this->firstname = $firstname ;}
    public function setlastname($lastname){ $this->lastname = $lastname ;}
    public function setemail($email){ $this->email = $email ;}
    public function setroleId($roleId){ $this->roleId = $roleId ;}
    public function setIdNumber($IdNumber){ $this->IdNumber = $IdNumber ;}
    public function setPhoneNumber($PhoneNumber){ $this->PhoneNumber = $PhoneNumber ;}
    public function setdb(PDO $db){ $this->db = $db; }
    public function setconnected($connected){ $this->connected = $connected; }
    public function setpassword($password){ $this->password = $password;}
    public function setauthenticated($authenticated){ return $this->authenticated = $authenticated ;}

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
            $actual_date = date('y-m-d h:i:s' , strtotime('now'));
            try{
                $requete = $this->db->prepare('INSERT INTO visitors (firstname , lastname , email , roleId , IdNumber , PhoneNumber , connected) VALUES(:firstname , :lastname , :email , :roleId , :IdNumber , :PhoneNumber , :connected)
                ');
                $requete->execute(array(
                    'firstname'=>$this->firstname(),
                    'lastname'=>$this->lastname(),
                    'email'=>$this->email(),
                    'roleId'=>$this->roleId(),
                    'IdNumber'=>$this->IdNumber(),
                    'PhoneNumber'=>$this->PhoneNumber(),
                    'connected'=>$this->connected()
                ));

                $visitor_id = $this->db->lastInsertId();
                
                $requete = $this->db->prepare('INSERT  INTO timesvisit (visitor_id , created_at)  VALUES(:visitor_id , :created_at) ');
                $requete->execute(array(
                    'created_at'=> $actual_date,
                    'visitor_id'=> $visitor_id
                ));

                $body = 'Hi, you are connected';
                $this->sendEmail($this->email() , $body);
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
        $actual_date = date('y-m-d h:i:s' , strtotime('now'));
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

        $requete = $this->db->prepare('INSERT  INTO timesvisit (visitor_id , created_at)  VALUES(:id , :created_at)');
        $requete->execute(array(
            'created_at'=>$actual_date,
            'id'=>$id
        ));

        $body = 'Hi, you are connected';
        $this->sendEmail($this->email() , $body);
    }

    /// Disconnect a visitor
    public function disconnect($id){
        $actual_date = date('y-m-d h:i:s' , strtotime('now'));
        $requete = $this->db->prepare('UPDATE visitors set connected=:connected WHERE id=:id');
        $requete->execute(array(
            'connected'=>$this->connected(),
            'id'=>$id
        ));

        $requete = $this->db->prepare('UPDATE timesvisit set updated_at=:updated_at WHERE visitor_id=:id AND updated_at IS NULL');
        $requete->execute(array(
            'updated_at'=>$actual_date,
            'id'=>$id
        ));

        $requete = $this->db->prepare('SELECT * FROM visitors WHERE id=:id LIMIT 1');
        $requete->execute(array(
            'id'=>$id
        ));

        $body = 'Hi, you are deconnected';
        $donnees = $requete->fetch();
        $this->sendEmail($donnees['email'] , $body);
    }

    /// Send Email
    public function sendEmail($email , $body){
        $this->includes();
        $mail = new PHPMailer(true);

        try{

            $mail ->isSMTP();
            $mail ->Host = 'smtp.gmail.com';
            $mail ->SMTPAuth = true;
            $mail ->Username = 'levyjaychris@gmail.com';      /// Your gmail
            $mail ->Password = 'kfhkehuezvtivntu';   ////// your gmail app password
            $mail ->SMTPSecure = 'ssl';
            $mail ->Port = 465;
            
            $mail ->setFrom('levyjaychris@gmail.com');  //// your gmail
            $mail ->addAddress($email);
            $mail ->isHTML(true);
            $mail->Subject = 'A User is connected';
            $mail->Body = $body;
            $mail->send();
        }catch(Exception $e){
           echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    /// import mailer dependencies
    private function includes(){
        require 'PHPMailer-master/src/Exception.php';
        require 'PHPMailer-master/src/PHPMailer.php';
        require 'PHPMailer-master/src/SMTP.php';
    }

    /// List of visitors
    public function list_visitors(){
        $requete = $this->db->prepare('SELECT * FROM visitors as Visitors
        LEFT JOIN roles as Roles 
        ON Visitors.roleId = Roles.id
        LEFT JOIN timesvisit as visits
        ON Visitors.id = visits.visitor_id
        ');
        $requete->execute();
        while($donnees = $requete->fetch()){
            ?>
                <tr>
                    <td><?php echo $donnees['firstname']; ?></td>
                    <td><?php echo $donnees['lastname']; ?></td>
                    <td><?php echo $donnees['name']; ?></td>
                    <td><?php echo $donnees['email']; ?></td>
                    <td><?php echo $donnees['IdNumber']; ?></td>
                    <td><?php echo $donnees['PhoneNumber']; ?></td>
                    <td><?php echo $donnees['created_at']; ?></td>
                    <td><?php echo $donnees['updated_at'] === null ? 'Pending ...' : $donnees['updated_at'] ?></td>
                    <td><a href="edit_visitor.php?id=<?=md5($donnees[0])?>" class="edit_button">Edit</button></td><td><button class="del_button">Delete</button></td>
                </tr>
            <?php
        }
    }

    //// Get a visitor
    public function getvisitor($id){
        $requete=$this->db->prepare('SELECT * FROM visitors WHERE md5(id)=:id LIMIT 1');
        $requete->execute(array(
            'id'=>$id
        ));
        return  $requete->fetch();
    }

    //Be a User
    public function BeUser($id){
        $requete = $this->db->prepare('UPDATE visitors SET  password=:password , authenticated=:authenticated WHERE md5(id)=:id');
        $requete->execute(array(
            'id' =>$id,
            'password'=>$this->password(),
            'authenticated'=>$this->authenticated()
        ));
        echo json_encode(true);
    }
}
?>

<?php 
    include_once 'objet.php';
    $visitors = new visitors($db);
    $error = array();
    $Error = array();

    /// set properties of class
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

    if(isset($_POST['password']) && !empty($_POST['password'])){
        $visitors->setpassword($_POST['password']);
    }elseif (isset($_POST['password']) && empty($_POST['password'])) {
        $error +=['password' => "password is empty !"];
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
  
    /// Exection of class functions
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

    }elseif(empty($error) && isset($_POST['beUser'])){

        $visitors->setpassword($_POST['password']);
        $visitors->setauthenticated(2);
        $visitors->BeUser($_POST['visitor']);
    }   
    elseif(!empty($error)){
        $Error['error'] = $error;
        echo json_encode($Error);
    }
?>