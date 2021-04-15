<?php
//Getting a Staking address
if(!isset($_COOKIE['StakeAddr'])){
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
    $stmt = $pdo->query('SELECT COUNT(`Addr`) as count FROM `ColdStake`');
    $Max = $stmt->fetch();
    $RandId = rand(1,$Max['count']);
    
    
    $stmt = $pdo->query('SELECT `Addr` as addr FROM `ColdStake` WHERE `AddrId` =  ?');
    $stmt->execute([$RandId]);
    $StakeAddr = $stmt->fetch();
    $ShowStake = $StakeAddr["addr"];
    setcookie("StakeAddr", $ShowStake, time()+60*60*24*365, '/');
}else{
    //Already received a address and the cookie is still there
    $ShowStake = $_COOKIE['StakeAddr'];
}
//Curl get transaction data to show delegation conformations
$TxID = 'e75e30354573bb9f69ad3c79e9f6737d861abae23fedb6507cfbbe88236c36e0';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://explorer.dogec.io/api/v2/tx/'.$TxID,
    CURLOPT_USERAGENT => 'Codular Sample cURL Request'
]);
$resp = curl_exec($curl);
curl_close($curl);



include('inc_functions.php');
include('inc_doctype.php');
$cfgNavClass = 'alt light';
?>
<title>The Features of DogeCash (DOGEC)</title>
<meta name="description" content="DogeCash masternode coin is based on Transparency, Governance, Community, Quality and Charity." />
<?php include('inc_head.php'); ?>
<link rel="stylesheet" type="text/css" href="assets/css/features.min.css">
<?php include('inc_header.php'); ?>
<script>
var i = 0;
function Switch(){
    i++;
    if(i === 1){
        var replace = "Make sure your wallet is fully synced with the network";
        fadeAndReplace(replace);
    }else if(i === 2){
        var replace = "Enable cold staking in your wallet by clicking on the snowflake.<br>You can create a receiving address by going to the receive tab of your wallet and generating a new address (optional) <br> (If you don't do this it will be automaticly generated later)";
        fadeAndReplace(replace);
    }else if(i === 3){
        var replace = "Go to the Cold Staking tab of your wallet.<br>Click on the Delegation tab at the top of the Cold Staking page.<br> Under 'add the staking address' you can use this cold staking address here:<br>"+'<?php echo"$ShowStake";?>' + "<br>or make/find you own";
        fadeAndReplace(replace);
    }else if(i === 4){
        var replace = "Now you can add a label if you want.<br>or leave it blank and it will automaticly create a new one.";
        fadeAndReplace(replace);
    }else if(i===5){
        var replace = "Now under 'Owner Address' put in the receiving address you created or leave it blank for one to be auto generated.";
        fadeAndReplace(replace);
    }else{
        var replace = "Thank you, you are all set<br> Cold Staking address: "+'<?php echo"$ShowStake";?>';
        fadeAndReplace(replace);
        $('#continue').fadeOut('slow');
    }
    //fadeAndReplace(i);
}
function fadeAndReplace(content){
    $('#WalkThrough').fadeOut('slow', function() {
        $('#WalkThrough').html(content);
        $('#WalkThrough').fadeIn('slow');
    });
}
</script> 
<div id="hero" class="dark-bg d-flex align-items-center">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
                <div id='WalkThrough'>
                    Here we will walk you through the process of using cold wallet staking 
                </div>
                <button id='continue' onclick='Switch()'>Continue</button>
			</div>
		</div>
	</div>
</div>

<?php include('inc_footer.php'); ?>
<?php include('inc_foot.php'); ?>

</html>