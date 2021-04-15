<?php
$host = '';
$db   = '';
$user = '';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$stmt = $pdo->query("SELECT * FROM `Banner` WHERE (`Started`+`Lasts`)> UNIX_TIMESTAMP() ORDER BY `Banner`.`Id`  DESC LIMIT 1");
$BannerResults = $stmt->fetch();
$BannerTitle = htmlspecialchars($BannerResults['Title'], ENT_QUOTES, 'UTF-8');
$BannerId = htmlspecialchars($BannerResults['Id'], ENT_QUOTES, 'UTF-8');
$BannerDesc = htmlspecialchars($BannerResults['Description'], ENT_QUOTES, 'UTF-8');
if ($stmt->rowCount()==0){
}else{
?>
<style>
.fragment {
    position: relative;
    width: 90%;
    font-size: 12px;
    font-family: tahoma;
    height: 140px;
    border: 1px solid #ccc;
    color: #555;
    display: block;
    padding: 10px;
    box-sizing: border-box;
    text-decoration: none;
    box-shadow: 2px 2px 5px rgba(0,0,0,.1);
    z-index:100;
}

.fragment:hover {
    box-shadow: 2px 2px 5px rgba(0,0,0,.2);

}

.fragment img { 
    float: left;
    margin-right: 10px;
}


.fragment h3 {
    padding: 0;
    margin: 0;
    color: #369;
}
.fragment h4 {
    padding: 0;
    margin: 0;
    color: #000;
}
#close {
    float:right;
    display:inline-block;
    padding:2px 5px;
    background:#ccc;
}
</style>
<div style="position:fixed; z-index: 1000000; background-color: white;width:100%;">
    <div class="fragment" id='Banner' style='display:none;'>
            <span id='close' onclick="BannerClose();">x</span>
                    <h2><?php echo$BannerTitle; ?></h2>
                    <p class="text">
                        <h3>
                            <?php echo$BannerDesc; ?>
                        </h3>
                    </p>
    </div>
</div>
<script>                
if (localStorage.getItem("Banner") == "closed" && localStorage.getItem("BannerID") == "<?php echo $BannerId; ?>") {  
}else{
    if(localStorage.getItem("BannerID") == "<?php echo $BannerId; ?>" || localStorage.getItem("BannerID") === null){
        document.getElementById('Banner').style.display="block";
    }else if(localStorage.getItem("BannerID") !== "<?php echo $BannerId; ?>"){
        localStorage.removeItem("BannerID");
        localStorage.removeItem("Banner");
        document.getElementById('Banner').style.display="block";
    }
}
function BannerClose(){
    document.getElementById('Banner').style.display="none";
    localStorage.setItem("Banner", "closed");
    localStorage.setItem("BannerID", "<?php echo$BannerId; ?>");
}
var t = localStorage.getItem("Banner");
var t1 = localStorage.getItem("BannerID");
console.log(t + " : " + t);
</script>
<?php

}