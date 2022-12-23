<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./css/visit.css" />
    <link rel="stylesheet" href="./js/datetimepicker-master/jquery.datetimepicker.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <script src='https://kit.fontawesome.com/a076d05399.js'></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="./js/datetimepicker-master/build/jquery.datetimepicker.full.min.js"></script>
    <title>Vilog - List of visitors</title>
  </head>
  <body>
        <?php 
           include_once 'objet.php';
           include_once 'visitors.class.php';
           include_once 'roles.class.php';
           $roles = new roles($db);
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
                <?php $visitors->countRoles(); ?>
            </div>
            <div class="row mt-5">
              <div class="col">
                <?php 
                  $roles->selectroles();
                ?>
              </div>
              <div class="col">
                From
                <input name="From" type="text" class="form-control">
              </div>
              <div class="col">
                to
                <input name="to" type="text" class="form-control">
              </div>
              <div class="col gap-2 mx-auto">
                <button id="search" type="button" class="btn btn-success fw-bolder text-light  px-4">Search</button>
              </div>
              <div class="col gap-2 mx-auto">
                <form action="visitors.class.php" method="post">
                  <input style="display:none" name="role" type="text" class="form-control">
                  <input style="display:none" name="from_print" type="text" class="form-control">
                  <input style="display:none;" name="to_print" type="text" class="form-control">
                  <button id="print" name="print_report" type="submit" class="btn btn-primary fw-bolder text-light  px-4">Print to Pdf</button>
                </form>
        
              </div>
             
            </div>

            <div id="cont_table" class="cont_table">
            <table id="table" class="table">
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
                <?php 
                $visitors->list_visitors();
                $visitors->list_employees();
                ?>
            </table>
            </div>
        </div>
  </body>
  </html>
  <script>
      $(document).ready(function(){
        var from = $("input[name='From']");
        var to = $("input[name='to']");
        from.datetimepicker();
        to.datetimepicker();
        var datas = {visitors:""};

        function get_visitors(){
          $.post(
            'visitors.class.php',
            datas,
            function(data){
              data = $.parseJSON(data);
              $("#list_visitors").html("");
                for(let i=0; i < data.length;i++){
                  $("#list_visitors").append("<tr id='visitor_"+ data[i].id+"'><td>"+ data[i].firstname +"</td></tr>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].lastname +"</td>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].name +"</td>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].email +"</td>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].IdNumber +"</td>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].PhoneNumber +"</td>");
                  $("#visitor_"+ data[i].id).append("<td>"+ data[i].created_at +"</td>");
                  if(data[i].updated_at !== null)
                    $("#vsitor_"+ data[i].id).append("<td>"+data[i].updated_at +"</td>");
                  else
                    $("#visitor_"+ data[i].id).append("<td>Pending ...</td>");
                }
                if(data.length == 0)
                  $("#list_visitors").append("<tr><td colspan='8'style='text-align:center;'>No Visitors found ...</td></tr>");
            }
          )
        }
        setInterval(function(){get_visitors()} , 1000);

        /*dtas = {employees : ""};
        function get_employees(){
          $.post(
            'visitors.class.php',
            dtas,
            function(data){
              data = $.parseJSON(data);
              $("#list_employees").html("");
                for(let i=0; i < data.length;i++){
                  $("#list_employees").append("<tr id='employee_"+ data[i].id+"'><td>"+ data[i].firstname +"</td></tr>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].lastname +"</td>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].name +"</td>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].email +"</td>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].IdNumber +"</td>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].PhoneNumber +"</td>");
                  $("#employee_"+ data[i].id).append("<td>"+ data[i].created_at +"</td>");
                  if(data[i].updated_at !== null)
                    $("#employee_"+ data[i].id).append("<td>"+data[i].updated_at +"</td>");
                  else
                    $("#employee_"+ data[i].id).append("<td>Pending ...</td>");
                }
                if(data.length == 0)
                  $("#list_employees").append("<tr><td colspan='8'style='text-align:center;'>No Visitors found ...</td></tr>");
            }
          )
        }
        setInterval(function(){get_employees()} , 1000);*/

        $('#logout').on('click' , function(e){
          e.preventDefault();
          var datas ={logout:$(this).text()}
          $.post(
            'visitors.class.php',
            datas,
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
          $("#report").html("");
        });

        $("#employees").on("click" , function(e){
          e.preventDefault();
          $("#list_visitors").css("display" , "none");
          $("#report").html("");
          $("#list_employees").css("display" , "");
        });

        $("#search").on("click",  function(e){
          e.preventDefault();
          var from = $("input[name='From']").val().replace(/[/_]/g , "-");
          var to = $("input[name='to']").val().replace(/[/_]/g , "-");
          var role = $("#role").val();
          $("input[name='from_print']").val(from);
          $("input[name='to_print']").val(to);
          $("input[name='role']").val(role);
          var datas = {role :role, from : from , to :to , search_report : $("#search").text()};
            $.post(
              'visitors.class.php',
              datas,
              function(data){
                data = $.parseJSON(data);
                $("#list_visitors").css("display" , "none");
                $("#list_employees").css("display" , "none");
                $(".table").append("<tbody id='report'></tbody>");
                $("#report").html("");
                for(let i=0; i < data.length;i++){
                  $("#report").append("<tr id='line_"+ data[i].id+"'><td>"+ data[i].firstname +"</td></tr>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].lastname +"</td>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].name +"</td>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].email +"</td>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].IdNumber +"</td>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].PhoneNumber +"</td>");
                  $("#line_"+ data[i].id).append("<td>"+ data[i].created_at +"</td>");
                  if(data[i].updated_at !== null)
                    $("#line_"+ data[i].id).append("<td>"+data[i].updated_at +"</td>");
                  else
                    $("#line_"+ data[i].id).append("<td>Pending ...</td>");
                }
                if(data.length == 0)
                  $("#report").append("<tr><td colspan='8'style='text-align:center;'>No Visitors Found ...</td></tr>");
              }
            )
        });

      });
  </script>