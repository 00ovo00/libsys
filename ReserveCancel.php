<?
// 이전 페이지 데이터 세션으로 받아오기
echo iconv("EUC-KR", "UTF-8", $row['BARCODENUM']);

session_start();
$ID = $_SESSION["id"];
$bNum = $_GET["barcodenum"];

// User table 연결
function connect_user_table($ID)
{
    global $connect;
    global $userData;
    global $userNum;

    $query="select * from user";
    $userData = mysql_query($query, $connect);
    $userNum = mysql_num_rows($userData);

}
//Book table 연결
function connect_book_table($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;

    $query="select * from book";
    $bookData = mysql_query($query, $connect);
    $bookNum = mysql_num_rows($bookData);

}
// Record table 연결
function connect_reserve_record_table($bNum)
{
    global $connect;

    $query = "delete from RESERVE_RECORD where BARCODENUM='{$bNum}'";
    // RESERVENUM(예약기록식별번호)은 record 생성시 auto_increment로 자동 생성하므로 공백으로 남김
    mysql_query($query,$connect);
}
 // 사용자 유효성 검사
 function ck_user($ID)
 {
     global $connect;
     global $userData;
     global $userNum;
     global $rrDate;
     global $overdueCount;
 
     connect_user_table($ID);
 
    // 데이터베이스 레코드들을 차례로 반복하여 검사
    for($i=0; $i<$userNum; $i++) {
        $userRecord = mysql_fetch_row($userData);
        if($ID == $userRecord[0]){     // 사용자 정보 데이터베이스의 첫번째 열 정보가 ID값이라고 설정했을때
                                             // 사용자 정보가 일치하면
            // 연체 정보 확인
            $overdueCount = $userRecord[9] - $rrDate - $userRecord[10];    // 마지막 반납일자와 연체일수를 계산하여 남은 연체일수 업데이트
                                                                            // 업데이트할 연체일수  = 이전 연체일수 - (현재날짜 - 마지막으로 반납한 날짜)
            if($overdueCount > 0) {     // 연체일수가 남아있으면
                $query = "update user set OVERDUECOUNT='{$overdueCount}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);  // 남은 연체일수 업데이트
            }
            else {                      // 연체일수가 0이하가 되면
                $overdueCount = 0;      // 연체일수를 0으로 설정(음수값 허용 x)
                $query = "update user set OVERDUECOUNT='{$overdueCount}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);
                $userRecord[7] = true;  // 대출 가능 상태로 변경
                $query = "update user set ISENABLE='{$userRecord[7]}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);
            }
            if ($userRecord[7] == true)     // 사용자가 대출 가능 상태이면
                return true;
            else {                           // 사용자가 대출 불가 상태이면
                return false;
            }      
        }
     }
 }
// 도서 정보 업데이트
function update_book_table($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;

    connect_book_table($bNum);

    for($i=0; $i<$bookNum; $i++) {
        $bookRecord = mysql_fetch_row($bookData);
        if($bNum == $bookRecord[0])       // 도서 정보 데이터베이스의 첫번째 열 정보가 바코드 번호라고 설정했을때
                                        // 도서 정보가 일치하면
            $bookRecord[8] = false;      // 상태를 미예약중으로 변경
            $query = "update book set ISRESERVED='{$bookRecord[8]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
    }
}

 // 데이터베이스 연결
 $database = "library_management";
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL 서버 연결 Error!");
 mysql_select_db($database, $connect);

 // 사용자 확인
 $isUser = ck_user($ID);    
 connect_reserve_record_table($bNum); // 예약 취소 승인(예약 데이터를 삭제)
 update_book_table($bNum);   // 예약 취소 정보 도서 DB에 업데이트
 // 예약 취소 정보 출력 위한 데이터 로드
 $query="select BNAME from book where BARCODENUM='{$bNum}'";
 $result = mysql_query($query, $connect);
 $resultrow = mysql_fetch_row($result);

 mysql_close($connect);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>도서 대출 시스템</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">


</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-fw fa-book"></i>
                </div>
                <div class="sidebar-brand-text mx-2">도서 대출 시스템</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-list"></i>
                    <span>도서 관리</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Components:</h6>
                        <a class="collapse-item" href="bookBorrow.php">도서 대출</a>
                        <a class="collapse-item" href="bookReserve.php">도서 예약</a>
                        <a class="collapse-item" href="bookReturn.php">도서 반납</a>
                        <a class="collapse-item" href="book_register.html">도서 등록</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    <span>통계</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Utilities</h6>
                        <a class="collapse-item" href="blank.php">추천 도서</a>
                        <a class="collapse-item" href="blank.php">독서 통계</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>


        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form method="POST" action="./search.php"
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." name="search"
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary">
			검색
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                         <!-- Nav Item - User Information -->
		<li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?echo($userid);?></span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="dashboard.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    <?echo($userid);?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <!-- Basic Card Example -->
                        <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><?echo($ID);?>님 <? echo iconv("EUC-KR", "UTF-8", "$resultrow[0]"); ?> 을(를) 예약 취소했습니다</h6>   
                                </div>
                        </div>
                    <div class="col-lg-6">


                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content

            <-- Footer -->
            <!-- <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Library Management System 2022</span>
                    </div>
                </div>
            </footer> -->
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">정말로 로그아웃 하시겠습니까?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">로그아웃 하시려면 아래 로그아웃 버튼을 누르세요</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">취소</button>
                    <a class="btn btn-primary" href="logout.php">로그아웃</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>
    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>