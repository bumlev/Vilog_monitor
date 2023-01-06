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
    <script src="js/datetimepicker-master/build/jquery.datetimepicker.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/md5.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <title>Vilog - List of visitors</title>
  </head>
  <body>
        <?php 
           include_once 'database/objet.php';
           include_once 'visitors.class.php';
           include_once 'roles/roles.class.php';
           $roles = new roles($db);
        ?>
        <nav class="nana">
              <a class="name_project" href="#"><i class="fa fa-desktop"></i>  ViLog</a>
              <ul class="list">
                <li><a id="visitors" href="#">Visitors</a></li>
                <?php 
                  if($_SESSION['authenticated'] == 1){
                ?>
                <li><a id="employees" href="#">Employees</a></li>
                <?php } ?>
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

            <div class="search_visitor">
              <input id="search_visitor" placeholder="search_visitor ..." name="search_visitor" type="text" class="form-control w-25 mt-2">
              <div id="items_visitor" class="list-group w-25 position-absolute mt-5 d-none">
              </div>
            </div>

            <div class="row mt-3">
              <div class="col">
                <?php 
                  $roles->selectroles();
                ?>
              </div>
              <div class="col">
                <input placeholder="From ..." name="From" type="text" class="form-control">
              </div>
              <div class="col">
                <input placeholder="to ..." name="to" type="text" class="form-control">
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
              <table class="table table-striped" id="list_visitors">
              </table>
              <table class="table table-striped" style="display:none;" id="list_employees">
              </table>
              <table class="table table-striped" style="display:none;" id="report">
              </table>
              <table class="table table-striped" style="display:none;" id="result_visitor">
              </table>
            </div>
        </div>

        <script>
          $(document).ready(function(event){
            localStorage.setItem("visitors", "");
            localStorage.setItem("employees", "none");
            localStorage.setItem("employees_wrapper" , "none");
            localStorage.setItem("visitors_wrapper" , "");
            var from = $("input[name='From']");
            var to = $("input[name='to']");
            from.datetimepicker();
            to.datetimepicker();
            var datas = {visitors:""};
            var md5 = function(value) {
              return CryptoJS.MD5(value).toString();
            }
            
            function hide_show(){
              $("#list_employees").css("display" , localStorage.getItem("employees") ? localStorage.getItem("employees"):"");
              $("#list_visitors").css("display" ,localStorage.getItem("visitors") ? localStorage.getItem("visitors"):"");
              $("#list_employees_wrapper").css("display" , localStorage.getItem("employees_wrapper") ? localStorage.getItem("employees_wrapper") : "");
              $("#list_visitors_wrapper").css("display" , localStorage.getItem("visitors_wrapper") ? localStorage.getItem("visitors_wrapper") : "");
            }
            setInterval(function(){hide_show()} , 0);


            function get_visitors(){
              $.post(
                'visitors.class.php',
                datas,
                function(data){
                  var testdata = data;
                  data = $.parseJSON(data);
                  if((localStorage.getItem('data_visitors') && localStorage.getItem('data_visitors') !== testdata) || $('#list_visitors')[0].childElementCount == 0){
                    localStorage.setItem('data_visitors' , testdata);
                    $("#list_visitors").html(data);
                    $('#list_visitors').DataTable().destroy();
                    $('#list_visitors').DataTable();
                  }
                  else if(!localStorage.getItem('data_visitors')){
                    $("#list_visitors").html(data);
                    $('#list_visitors').DataTable();
                    localStorage.setItem('data_visitors' , testdata);
                  } 
                }
              )
            }      
            setInterval(function(){get_visitors()} ,0);

            dtas = {employees : ""};
            function get_employees(){
              $.post(
                'visitors.class.php',
                dtas,
                function(data){
                  var testdata = data;
                  data = $.parseJSON(data);
                  if((localStorage.getItem('data_employees') && localStorage.getItem('data_employees') !== testdata) || $('#list_employees')[0].childElementCount == 0){
                    localStorage.setItem('data_employees' , testdata);
                    $("#list_employees").html(data);
                    $('#list_employees').DataTable().destroy();
                    $('#list_employees').DataTable();
                  }
                  else if(!localStorage.getItem('data_employees')){
                    $("#list_employees").html(data);
                    $('#list_employees').DataTable();
                    localStorage.setItem('data_employees' , testdata);
                  } 
                }
              )
            }
            setInterval(function(){get_employees()} ,0);
            
            $('#logout').on('click' , function(e){
              e.preventDefault();
              localStorage.clear();
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
              localStorage.setItem("visitors", "");
              localStorage.setItem("employees", "none");
              localStorage.setItem("employees_wrapper" , "none");
              localStorage.setItem("report_wrapper" , "none");
              localStorage.setItem("visitors_wrapper" , "");
              $("#list_employees").css("display" , "none");
              $("#list_employees_wrapper").css("display" , "none");
              $("#report_wrapper").css("display" , "none");
              $("#list_visitors_wrapper").css("display" , "");
              $("#list_visitors").css("display" , "");
              $("#report").css("display" , "none");
              $("#result_visitor").css("display" , "none");
            });

            $("#employees").on("click" , function(e){
              e.preventDefault();
              localStorage.setItem("visitors", "none");
              localStorage.setItem("employees", "");
              localStorage.setItem("employees_wrapper" , "");
              localStorage.setItem("visitors_wrapper" , "none");  
              localStorage.setItem("report_wrapper" , "none");
              $("#list_visitors").css("display" , "none");
              $("#list_employees_wrapper").css("display" , "");
              $("#list_visitors_wrapper").css("display" , "none");
              $("#report").css("display" , "none");
              $("#report_wrapper").css("display" , "none");
              $("#result_visitor").css("display" , "none");
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
                    localStorage.setItem("visitors", "none");
                    localStorage.setItem("employees", "none");
                    localStorage.setItem("employees_wrapper" , "none");
                    localStorage.setItem("visitors_wrapper" , "none");
                    $("#list_visitors").css("display" , "none");
                    $("#list_employees").css("display" , "none");
                    $("#list_employees_wrapper").css("display" , "none");
                    $("#list_visitors_wrapper").css("display" , "none");
                    $("#result_visitor").css("display" , "none");
                    $("#report").css("display" , "");
                    $("#report").html(data);
                    $("#report").DataTable().destroy();
                    $("#report").DataTable({
                      searching:true
                    });

                  }
                )
            });

            $("#search_visitor").on('keyup' , function(e){
                var name_visitor = $(this).val();
                $.post(
                  'visitors.class.php',
                  {
                    name_visitor:name_visitor
                  },
                  function(data){
                    data = $.parseJSON(data);
                    $("#items_visitor").removeClass("d-none");
                    $("#items_visitor").html("");
                    for(let i=0; i < data.length;i++){
                      $("#items_visitor").append('<a id="linkVisitor_'+ md5(data[i].id)+'" href="#" class="list-group-item list-group-item-action">'+ data[i].firstname +' '+data[i].lastname +'</a>');
                      $("#linkVisitor_"+ md5(data[i].id)).on("click" , function(e){
                          e.preventDefault();
                          $("#search_visitor").val($(this).text());
                          var search_visitor = md5(data[i].id);
                          $("#items_visitor").html("");
                          $.post(
                            'visitors.class.php',
                            {search_visitor},
                            function(data){
                              data = $.parseJSON(data);
                              localStorage.setItem("employees_wrapper" , "none");
                              localStorage.setItem("visitors_wrapper" , "none");
                              $("#list_employees_wrapper").css("display" , "none");
                              $("#list_visitors_wrapper").css("display" , "none");
                              $("#report_wrapper").css("display" , "none");
                              $("#result_visitor").css("display" , "");
                              $("#result_visitor").html(data);
                            }
                          )
                      });
                    }
                    if(data.length === 0){
                      $("#items_visitor").html("");
                      $("#items_visitor").append('<a href="#" class="list-group-item list-group-item-action">No Visitors Found ...</a>');
                    }

                    if(!data){
                      $("#items_visitor").html("");
                    }
                  }
                )
            });
          });

        </script>
  </body>
  </html>