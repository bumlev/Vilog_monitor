<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

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
    private $qrcode;
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
    public function qrcode(){ return $this->qrcode;}

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
    public function setauthenticated($authenticated){ $this->authenticated = $authenticated ;}
    public function setqrcode($qrcode){ $this->qrcode = $qrcode ;}

    // create a Visitor
    public function createVisitor(){
        $request = $this->db->prepare('SELECT COUNT(*) as nb FROM  visitors WHERE IdNumber=:idnumb or email=:email or PhoneNumber =:phone AND  PhoneNumber !="" LIMIT 1');
        $request->execute(array(
            'idnumb'=>$this->IdNumber(),
            'email'=>$this->email(),
            'phone'=>$this->PhoneNumber()
        ));

        $lines = $request->fetch();
        $row = $lines['nb'];

        if($row == 0)
        {
            $actual_date = date('y-m-d h:i:s' , strtotime('now'));
            try{
                $request = $this->db->prepare('INSERT INTO visitors (firstname , lastname , email , roleId , IdNumber , PhoneNumber , connected) VALUES(:firstname , :lastname , :email , :roleId , :IdNumber , :PhoneNumber , :connected)
                ');
                $request->execute(array(
                    'firstname'=>$this->firstname(),
                    'lastname'=>$this->lastname(),
                    'email'=>$this->email(),
                    'roleId'=>$this->roleId(),
                    'IdNumber'=>$this->IdNumber(),
                    'PhoneNumber'=>$this->PhoneNumber(),
                    'connected'=>$this->connected()
                ));

                $visitor_id = $this->db->lastInsertId();
                
                $request = $this->db->prepare('INSERT  INTO timesvisit (visitor_id , created_at)  VALUES(:visitor_id , :created_at) ');
                $request->execute(array(
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
        $request = $this->db->prepare('SELECT * FROM visitors as Visitors
        LEFT JOIN roles as Roles 
        ON Visitors.roleId = Roles.id
        WHERE IdNumber=:idnumber LIMIT 1');
        
        $request->execute(array(
            'idnumber'=>$this->IdNumber()
        ));

        $datas = $request->fetch();
        echo json_encode($datas);
    }

    // Update a visitor
    public function updateVisitor($id){
        $actual_date = date('y-m-d h:i:s' , strtotime('now'));
        $request= $this->db->prepare('UPDATE visitors set firstname=:firstname , lastname=:lastname , email=:email , roleId=:roleId , IdNumber=:idnumb , PhoneNumber=:phone , connected=:connected
         WHERE id=:id');

        $request->execute(array(
            'firstname'=>$this->firstname(),
            'lastname'=>$this->lastname(),
            'email'=>$this->email(),
            'roleId'=>$this->roleId(),
            'idnumb'=>$this->IdNumber(),
            'phone'=>$this->PhoneNumber(),
            'connected'=>$this->connected(),
            'id'=>$id
        ));

        $request = $this->db->prepare('INSERT  INTO timesvisit (visitor_id , created_at)  VALUES(:id , :created_at)');
        $request->execute(array(
            'created_at'=>$actual_date,
            'id'=>$id
        ));

        $body = 'Hi, you are connected';
        $this->sendEmail($this->email() , $body);
    }

    /// Disconnect a visitor
    public function disconnect($id){
        $actual_date = date('y-m-d h:i:s' , strtotime('now'));
        $request = $this->db->prepare('UPDATE visitors set connected=:connected WHERE id=:id');
        $request->execute(array(
            'connected'=>$this->connected(),
            'id'=>$id
        ));

        $request = $this->db->prepare('UPDATE timesvisit set updated_at=:updated_at WHERE visitor_id=:id AND updated_at IS NULL');
        $request->execute(array(
            'updated_at'=>$actual_date,
            'id'=>$id
        ));

        $request = $this->db->prepare('SELECT * FROM visitors WHERE id=:id LIMIT 1');
        $request->execute(array(
            'id'=>$id
        ));

        $body = 'Hi, you are deconnected';
        $datas = $request->fetch();
        $this->sendEmail($datas['email'] , $body);
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
        $request = $this->db->prepare('SELECT * FROM visitors as Visitors
            LEFT JOIN roles as Roles 
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.name !=:name
        ');

        $request->execute(array('name' => 'Employee'));$i=0;
        ?>
            <tbody id="list_visitors">
        <?php
        while($datas = $request->fetch()){
            $i++;
            ?>
                <tr class="list_visitors">
                    <td><?php echo $datas['firstname']; ?></td>
                    <td><?php echo $datas['lastname']; ?></td>
                    <td><?php echo $datas['name']; ?></td>
                    <td><?php echo $datas['email']; ?></td>
                    <td><?php echo $datas['IdNumber']; ?></td>
                    <td><?php echo $datas['PhoneNumber']; ?></td>
                    <td><?php echo $datas['created_at']; ?></td>
                    <td><?php echo $datas['updated_at'] === null ? 'Pending ...' : $datas['updated_at'] ?></td>
                    <td><a href="edit_visitor.php?id=<?=md5($datas[0])?>" class="edit_button">Edit</a></td><td><button class="del_button">Delete</button></td>
                </tr>
            <?php
        }
        ?>    
            </tbody>
        <?php
        if($i == 0){
            ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No Visitors found ...</td>
                </tr> 
            <?php
        }
    }

    // list of employees
    public function list_employees(){
        $request = $this->db->prepare('SELECT * FROM visitors as Visitors
            LEFT JOIN roles as Roles 
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.name =:name
        ');

        $request->execute(array('name' => 'Employee'));$i=0;
        ?>
            <tbody id="list_employees">
        <?php
        while($datas = $request->fetch()){
            $i++;
            ?>
                <tr>
                    <td><?php echo $datas['firstname']; ?></td>
                    <td><?php echo $datas['lastname']; ?></td>
                    <td><?php echo $datas['name']; ?></td>
                    <td><?php echo $datas['email']; ?></td>
                    <td><?php echo $datas['IdNumber']; ?></td>
                    <td><?php echo $datas['PhoneNumber']; ?></td>
                    <td><?php echo $datas['created_at']; ?></td>
                    <td><?php echo $datas['updated_at'] === null ? 'Pending ...' : $datas['updated_at'] ?></td>
                    <td><a href="edit_visitor.php?id=<?=md5($datas[0])?>" class="edit_button">Edit</a></td><td><button class="del_button">Delete</button></td>
                </tr>
            <?php
        }
        ?>    
            </tbody>
        <?php
        if($i == 0){
            ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No Visitors found ...</td>
                </tr> 
            <?php
        }
    }

    //// Get a visitor
    public function getvisitor($id){
        $request=$this->db->prepare('SELECT * FROM visitors WHERE md5(id)=:id LIMIT 1');
        $request->execute(array(
            'id'=>$id
        ));
        return  $request->fetch();
    }

    //Be a User
    public function BeUser($id){
        $request = $this->db->prepare('UPDATE visitors SET  password=:password , authenticated=:authenticated WHERE md5(id)=:id');
        $request->execute(array(
            'id' =>$id,
            'password'=>$this->password(),
            'authenticated'=>$this->authenticated()
        ));
        echo json_encode(true);
    }

    /// login as user 
    public function login(){
        $request = $this->db->prepare('SELECT * FROM visitors WHERE md5(email)=md5(:email) AND md5(password)=md5(:password) LIMIT 1');
        $request->execute(array(
            'email'=>$this->email(),
            'password'=>$this->password()
        ));

        $datas = $request->fetch();
        $row = $request->rowCount();
        if($row > 0){
            $_SESSION['id']= $datas['id'];
            $_SESSION['firstname']= $datas['firstname'];
            $_SESSION['lastname']= $datas['lastname'];
            echo json_encode(true);
        }
        else
            echo json_encode(false);
    }

    //logout
    public function logout(){
        session_destroy();
        unset($_SESSION['id']);
        unset($_SESSION['firstname']);
        unset($_SESSION['lastname']);
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

    if(isset($_POST['qrc']) && !empty($_POST['qrc'])){
        $visitors->setqrcode($_POST['qrc']);
    }elseif (isset($_POST['qrc']) && empty($_POST['qrc'])) {
        $error +=['qrcode' => "qrcode is empty !"];
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
    }elseif(empty($error) && isset($_POST['login'])){

        $visitors->setemail($_POST['email']);
        $visitors->setpassword($_POST['password']);
        $visitors->login();
    }elseif(empty($error) && isset($_POST['logout'])){
        $visitors->logout();
    }   
    elseif(!empty($error)){
        $Error['error'] = $error;
        echo json_encode($Error);
    }
?>