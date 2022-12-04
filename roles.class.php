<?php 

  class roles
  {
    private $name;
    private $db;


    function __construct($db){
        $this->setdb($db);
    }

    /// The Getters 

    public function name(){return $this->name;}
    public function db(){return $this->db; }

    
    /// The setters
    public function setname($name){
       $this->name = $name;
    }

    public function setdb(PDO $db){
        $this->db = $db;
    }

    public function selectroles(){
        $requet = $this->db->prepare('SELECT * from roles');
        $requet->execute()
        ?>
         <select name="role" id="role" placeholder="You are ....">
            <option selected="selected" value="<?php  echo(null); ?>"><?php echo htmlentities('I am  ...') ?></option>
            <?php 
                while($donnees=$requet->fetch()){
                    ?>
                        <option value="<?= $donnees["id"] ?>"><?php echo htmlentities($donnees['name'])?></option>
                    <?php
                }
            ?>   
        </select>
        <span id="error_role" class="error"></span>
        <?php
    }
  }
?>