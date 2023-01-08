<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            $timestamp = strtotime("-8 hours");
            $actual_date = date('Y-m-d H:i:s', $timestamp);
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

                $body = $this->firstname().' '.$this->lastname().' '.' is connected';
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
        $timestamp = strtotime("-8 hours");
        $actual_date = date('Y-m-d H:i:s', $timestamp);
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

        $body = $this->firstname().' '.$this->lastname().' '.'is connected';
        $this->sendEmail($this->email() , $body);
    }

    /// Disconnect a visitor
    public function disconnect($id){
        $timestamp = strtotime("-8 hours");
        $actual_date = date('Y-m-d H:i:s', $timestamp);
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

       
        $datas = $request->fetch();
        $body = $datas["firstname"].' '.$datas['lastname'].' '.'is deconnected';
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

    /// load visitors is the same like list_visitors and it is used by jquery
    public function load_visitors(){
        $timestamp = strtotime("-8 hours");
        $actual_date = date('Y-m-d H:i:s', $timestamp);
        $request = $this->db->prepare('SELECT * FROM visitors as Visitors
            LEFT JOIN roles as Roles 
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.name !=:name 
            AND DAY(visits.created_at) =:today
            ORDER BY visits.id
        ');

        $request->execute(array('name' => 'Employee' , 'today'=> date("d" , strtotime($actual_date))));
        $visitors = $request->fetchAll();

        $output.="
        <thead>
            <tr>
            <th scope='col'>Firstname</th>
            <th scope='col'>Lastname</th>
            <th scope='col'>Role</th>
            <th scope='col'>Email</th>
            <th scope='col'>IdNumber</th>
            <th scope='col'>PhoneNumber</th>
            <th scope='col'>Time in</th>
            <th scope='col'>Time out</th>
            </tr>
        </thead>
        <tbody>";    
        foreach($visitors as $visitor){
            $updated_at = $visitor['updated_at'] == NULL ? 'Pending ...' : $visitor['updated_at'];
            $output .='
                <tr>
                    <td>'.$visitor['firstname'].'</td>
                    <td>'.$visitor['lastname'].'</td>
                    <td>'.$visitor['name'].'</td>
                    <td>'.$visitor['email'].'</td>
                    <td>'.$visitor['IdNumber'].'</td>
                    <td>'.$visitor['PhoneNumber'].'</td>
                    <td>'.$visitor['created_at'].'</td>
                    <td>'.$updated_at.'</td>
                </tr>';
        }

        $output .='
            </tbody>
        ';
        echo json_encode($output);
       
    }
    /// get numbers of every roles
    public function countRoles(){
        $request = $this->db->prepare('SELECT COUNT(*) as nb_role , name FROM roles as Roles
            LEFT JOIN visitors as Visitors
            ON Roles.id = Visitors.roleId
            WHERE Roles.id = Visitors.roleId
            group by Roles.name
        ');

        $request->execute();
        $roles = $request->fetchAll();

        foreach($roles as $role){
            if($role["name"] == 'Visitor'){

                $output .='<div style="background-color:#00B4DB" class="count_visitor">
                            <div class="icon"><i class="fa fa-users"></i></div>
                        ';
            }elseif($role["name"] == 'Contractor'){
                
                $output .=' <div style="background-color:#FDC830" class="count_visitor">
                <div class="icon"><i class="fa fa-address-book"></i></div>
                        ';
            }elseif($role["name"] == "Personnal Visitor"){

                $output .=' <div style="background-color:#65cbf3" class="count_visitor">
                    <div class="icon"><i class="fa fa-user fa-2x"></i></div>
                        ';
            }elseif($role["name"] == "Employee"){

                $output .='<div style="background-color:#005AA7" class="count_visitor">
                <div class="icon"><i class="fas fa-id-card"></i></div>';
            }
            
            $output .=' <div class="number"><span>'.$role["name"].'</span> <span>'.$role["nb_role"].'</span></div>
            </div>  ';
        }

        echo json_encode($output);
    }

    // search visitor by name 
    public function searchVisitorByName($name){
        $pattern =  "%$name%";
        $request= $this->db->prepare("SELECT * FROM visitors 
            WHERE CONCAT(firstname , lastname) LIKE ?
        ");

        $request->execute(array($pattern));
        $visitor = $request->fetchAll();
        echo empty($name) ? json_encode(false) : json_encode($visitor);
    }
    

    public function load_employees(){
        $linesByPage = 2;
        $pageNow = isset($_GET['page']) ? $_GET['page'] : 1;
        $x = ($pageNow - 1) * $linesByPage;
        $y=$linesByPage;

        $timestamp = strtotime("-8 hours");
        $actual_date = date('Y-m-d H:i:s', $timestamp);
        $request = $this->db->prepare("SELECT * FROM visitors as Visitors
            LEFT JOIN roles as Roles 
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.name =:name 
            AND DAY(visits.created_at) =:today
            ORDER BY visits.id ASC
        ");

        $request->execute(array('name' => 'Employee' , 'today'=> date("d" , strtotime($actual_date))));
        $employees = $request->fetchAll();

        $output.="
        <thead>
            <tr>
            <th scope='col'>Firstname</th>
            <th scope='col'>Lastname</th>
            <th scope='col'>Role</th>
            <th scope='col'>Email</th>
            <th scope='col'>IdNumber</th>
            <th scope='col'>PhoneNumber</th>
            <th scope='col'>Time in</th>
            <th scope='col'>Time out</th>
            <th></th>
            </tr>
        </thead>
        <tbody class='body_tag'>";    
        foreach($employees as $employee){
            $updated_at = $employee['updated_at'] == NULL ? 'Pending ...' : $employee['updated_at'];
            $output .='
                <tr class="tr_tag">
                    <td>'.$employee['firstname'].'</td>
                    <td>'.$employee['lastname'].'</td>
                    <td>'.$employee['name'].'</td>
                    <td>'.$employee['email'].'</td>
                    <td>'.$employee['IdNumber'].'</td>
                    <td>'.$employee['PhoneNumber'].'</td>
                    <td>'.$employee['created_at'].'</td>
                    <td>'.$updated_at.'</td>
                    <td><a href="edit_visitor.php?id='.md5($employee[0]).'" class="edit_button">Set as a User<a></td>
                </tr>';
        }

        $output .='
            </tbody>
        ';
        echo json_encode($output);
    }

    //// Get a visitor
    public function getVisitor($id){
        $request=$this->db->prepare('SELECT * FROM visitors as Visitors 
        LEFT JOIN roles as Roles
        ON Visitors.roleId = Roles.id
        LEFT JOIN timesvisit as visits
        ON Visitors.id = visits.visitor_id
        WHERE md5(visits.visitor_id)=:id ORDER BY visits.id DESC LIMIT 1');
        $request->execute(array(
            'id'=>$id
        ));
        $output.="
        <thead>
            <tr>
            <th scope='col'>Firstname</th>
            <th scope='col'>Lastname</th>
            <th scope='col'>Role</th>
            <th scope='col'>Email</th>
            <th scope='col'>IdNumber</th>
            <th scope='col'>PhoneNumber</th>
            <th scope='col'>Time in</th>
            <th scope='col'>Time out</th>
            </tr>
        </thead>
        <tbody>";

        $visitor = $request->fetch();
        $updated_at = $visitor['updated_at'] == NULL ? 'Pending ...' : $visitor['updated_at'];
        $isEmployee = $visitor['name'] == 'Employee' ? '<td><a href="edit_visitor.php?id='.md5($visitor[0]).'" class="edit_button">Set as a User</a></td>' : "";
        $output .='
                <tr>
                    <td>'.$visitor['firstname'].'</td>
                    <td>'.$visitor['lastname'].'</td>
                    <td>'.$visitor['name'].'</td>
                    <td>'.$visitor['email'].'</td>
                    <td>'.$visitor['IdNumber'].'</td>
                    <td>'.$visitor['PhoneNumber'].'</td>
                    <td>'.$visitor['created_at'].'</td>
                    <td>'.$updated_at.'</td>
                    '.$isEmployee.'
                </tr>';
                $output .='
        </tbody>
        ';
        echo json_encode($output);
    }

    /// Identify a visitor
    public function identifyVisitor($id){
        $request=$this->db->prepare('SELECT * FROM visitors as Visitors 
        LEFT JOIN roles as Roles
        ON Visitors.roleId = Roles.id
        LEFT JOIN timesvisit as visits
        ON Visitors.id = visits.visitor_id
        WHERE md5(visits.visitor_id)=:id ORDER BY visits.id DESC LIMIT 1');
        $request->execute(array(
            'id'=>$id
        ));

        return $request->fetch();
    }

    // get a report by role and time for visitore
    public function report($arrival_date , $depart_date){
        $request = $this->db->prepare("SELECT * FROM visitors AS Visitors
            LEFT JOIN roles as Roles
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.id =:roleId
            AND visits.created_at BETWEEN :arrival_date AND :depart_date
        ");

        $request->execute(array(
            'arrival_date'=>$arrival_date,
            'depart_date' =>$depart_date,
            'roleId' =>$this->roleId()
        ));

        $visitors = $request->fetchAll();
        $output.="
        <thead>
            <tr>
            <th scope='col'>Firstname</th>
            <th scope='col'>Lastname</th>
            <th scope='col'>Role</th>
            <th scope='col'>Email</th>
            <th scope='col'>IdNumber</th>
            <th scope='col'>PhoneNumber</th>
            <th scope='col'>Time in</th>
            <th scope='col'>Time out</th>
            </tr>
        </thead>
        <tbody>";    
        foreach($visitors as $visitor){
            $updated_at = $visitor['updated_at'] == NULL ? 'Pending ...' : $visitor['updated_at'];
            $output .='
                <tr>
                    <td>'.$visitor['firstname'].'</td>
                    <td>'.$visitor['lastname'].'</td>
                    <td>'.$visitor['name'].'</td>
                    <td>'.$visitor['email'].'</td>
                    <td>'.$visitor['IdNumber'].'</td>
                    <td>'.$visitor['PhoneNumber'].'</td>
                    <td>'.$visitor['created_at'].'</td>
                    <td>'.$updated_at.'</td>
                </tr>';
        }

        $output .='
            </tbody>
        ';
        echo json_encode($output);
    }

    /// get a print report
    public function print_report($arrival_date , $depart_date){
        $arrival_date = str_replace("/" , "-" , $arrival_date);
        $depart_date = str_replace("/" , "-" , $depart_date);

        $request = $this->db->prepare("SELECT * FROM visitors AS Visitors
            LEFT JOIN roles as Roles
            ON Visitors.roleId = Roles.id
            LEFT JOIN timesvisit as visits
            ON Visitors.id = visits.visitor_id
            WHERE Roles.id =:roleId
            AND visits.created_at BETWEEN :arrival_date AND :depart_date
        ");
        $request->execute(array(
            'arrival_date'=>$arrival_date,
            'depart_date' =>$depart_date,
            'roleId' =>$this->roleId()
        ));

        $visitors = $request->fetchAll();
        $this->generatePDF($visitors);
    }

    /// Generate a PDF
    public function generatePDF($visitors){
        ob_start();
        include_once 'pdfContent.php';
        $html = ob_get_contents();
        ob_end_clean();
    
        require_once 'dompdf/autoload.inc.php';
        $options = new Options();
        $options->set("defaultFont" , "Courier");
    
        $dompdf = new Dompdf($options);
        
    
        $dompdf->loadHtml($html);
        $dompdf->setPaper("A4" , "portrait");
        $dompdf->render();
        $fichier = "monfichier.pdf";
        $dompdf->stream($fichier);
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
            $_SESSION['authenticated'] = $datas["authenticated"];
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
        unset($_SESSION['authenticated']);
        echo json_encode(true);
    }
}
?>

<?php 
    include_once 'database/objet.php';
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

    if(isset($_POST['user']) && $_POST['user'] == "true"){
        $visitors->setauthenticated(2);
    }elseif (isset($_POST['admin']) && $_POST['admin'] == "true"){
        $visitors->setauthenticated(1);
    }elseif (isset($_POST['user']) && isset($_POST['admin']) && $_POST['user'] == "false" && $_POST['admin'] == "false" ){
        $error +=['radio' => "set a user or an admin !"];
    }


    if(isset($_POST['from']) && isset($_POST['to']) && empty($_POST['from']) && empty($_POST['to'])){
        $error +=['report_date' => 'your arrival_date or depart_date is empty'];
    }

    if(isset($_POST['from_print']) && isset($_POST['to_print']) && empty($_POST['from_print']) && empty($_POST['to_print'])){
        $error +=['report_date' => 'your arrival_date or depart_date is empty'];
    }


    /// Execution of class functions
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

        $visitors->BeUser($_POST['visitor']);
    }elseif(empty($error) && isset($_POST['login'])){

        $visitors->setemail($_POST['email']);
        $visitors->setpassword($_POST['password']);
        $visitors->login();
    }elseif(empty($error) && isset($_POST['logout'])){
        $visitors->logout();
    }elseif(empty($error) && isset($_POST['search_report'])){

        $visitors->report($_POST['from'] , $_POST['to']);
    }elseif(empty($error) && isset($_POST['print_report'])){
        $visitors->print_report($_POST['from_print'] , $_POST['to_print']);
    }elseif(isset($_POST['visitors'])){

        $visitors->load_visitors();
    }elseif(isset($_POST['employees'])){

        $visitors->load_employees();
    }elseif(isset($_POST['name_visitor'])){

        $visitors->searchVisitorByName($_POST['name_visitor']);
    }elseif(isset($_POST['search_visitor'])){

        $visitors->getVisitor($_POST['search_visitor']);
    }elseif(isset($_POST['Roles'])){

        $visitors->countRoles();
    }    
    elseif(!empty($error)){
        $Error['error'] = $error;
        echo json_encode($Error);
    }
?>