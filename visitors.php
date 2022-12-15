<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./css/visit.css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <title>Vilog - List of visitors</title>
  </head>
  <body>
        <?php 
           include_once 'objet.php';
           include_once 'visitors.class.php';
        ?>
        <nav class="nana">
              <a class="name_project" href="#"><i class="fa fa-desktop"></i>  ViLog</a>
              <ul class="list">
                <li><a id="visitors" href="#">Visitors</a></li>
                <li><a id="employees" href="#">Employees</a></li>
                <li><?php echo $_SESSION['firstname'].'  '.$_SESSION['lastname'] ?></li>
                <li><a id="logout" class="logout" href="#">logout</a></li>
              </ul>
        </nav>
        <?php  
          if(!isset($_SESSION['firstname']) && !isset($_SESSION['lastname'])){
            echo '<script>document.location.href="index.php"</script>';
          }
        ?>
        <div class="contain">
            <div class="account">
                <div style="background-color:#00B4DB" class="count_visitor">
                    <div class="icon"><i class="fa fa-user fa-2x"></i></div>
                    <div class="number"><span>Visitors</span> <span>1</span></div>
                </div>
                <div style="background-color:#FDC830" class="count_visitor">
                    <div class="icon"><i class="fa fa-user fa-2x"></i></div>
                    <div class="number"><span>Visitors</span> <span>1</span></div>
                </div>
                <div style="background-color:#65cbf3"  class="count_visitor">
                    <div class="icon"><i class="fa fa-user fa-2x"></i></div>
                    <div class="number"><span>Visitors</span> <span>1</span></div>
                </div>
                <div style="background-color:#005AA7" class="count_visitor">
                    <div class="icon"><i class="fa fa-user fa-2x"></i></div>
                    <div class="number"><span>Visitors</span> <span>1</span></div>
                </div> 
            </div>

            <div class="cont_table">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Firstname</th>
                    <th scope="col">Lastname</th>
                    <th scope="col">Role</th>
                    <th scope="col">Email</th>
                    <th scope="col">IdNumber</th>
                    <th scope="col">PhoneNumber</th>
                    <th scope="col">Time in</th>
                    <th scope="col">Time out</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $visitors->list_visitors();
                    $visitors->list_employees();
                  ?>
                </tbody>
              </table>
            </div>
        </div>
  </body>
  </html>

  <script>
      $(document).ready(function(){
        $("#list_employees").css("display" , "none");
        $('#logout').on('click' , function(e){
          e.preventDefault();
          var donnees ={logout:$(this).text()}
          $.post(
            'visitors.class.php',
            donnees,
            function(data){
              if(data)
                window.location.href=" http://localhost/Vilog/";
            }
          )
        });

        $("#visitors").on("click" , function(e){
          e.preventDefault();
          $("#list_employees").css("display" , "none");
          $("#list_visitors").css("display" , "");
          $("#list_visitors").load(self);
        });

        $("#employees").on("click" , function(e){
          e.preventDefault();
          $("#list_visitors").css("display" , "none");
          $("#list_employees").css("display" , "");
        });

      })

  </script>