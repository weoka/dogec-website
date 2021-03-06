<?php
include('inc_functions.php');
include('inc_doctype.php');
?>
<title>DogeCash (DOGEC) - A Masternode for Everyone!</title>
<meta name="description" content="DogeCash is a transparent, community-governed cryptocurrency utilized by its high-quality platforms." />
<?php include('inc_head.php'); ?>
<link rel="stylesheet" type="text/css" href="assets/css/home.min.css">
<?php include('inc_headert.php'); ?>
<!-- We don't need this anymore ..
<iframe width="358" height="184" src="https://w2.countingdownto.com/2880659" frameborder="0"></iframe>-->
<!-- Hero -->
<div id="hero">
	<div class="container-fluid p-0" data-aos="flip-left">
		<div class="row">
			<div class="col-12">
                <div id="home_features" class="d-none d-md-block">
                    <img data-lazy-src="assets/images/features_home_01.jpg" />
                    <img data-lazy-src="assets/images/features_home_02.jpg" />
                    <img data-lazy-src="assets/images/features_home_03.jpg" />
                    <img data-lazy-src="assets/images/features_home_04.jpg" />
                    <img data-lazy-src="assets/images/features_home_05.jpg" />
                </div>
                <img src="assets/images/hero-mobile.jpg" class="img-fluid d-md-none">
            </div>
		</div>
	</div>
</div>

<!-- Hero Bar -->
<div id="hero-bar" class="container">
    <div class="row" data-aos="slide-up">
        <div class="col-12 text-center">
            <p>DogeCash™ is a project of trust, tolerance and integration where people from every corner of the world work together towards a common goal: Developing an ecosystem of services backed by a top notch cryptocurrency that belongs not only to a few but, to every single investor involved.</p>
        </div>
    </div>
</div>

<!-- Section 01 -->
<div id="section01" class="container-fluid black-pattern-bg pl-0 pr-0">
	<div id="block-a" class="container" data-aos="fade-up">
		<div class="row">
			<div class="col-12">
                <div id="logobar" class="d-flex justify-content-between align-items-center">
                    <a href="https://cmc.dogec.io/" target='_blank'><img class="img-fluid" src="assets/images/logo-coinmarketcap.svg"></a>
                    <a href="https://stakecube.net/app/exchange/" target='_blank'><img class="img-fluid" src="assets/images/grey-stakecube_logo_775x200.png"></a>
                    <a href="https://info.binance.com/en/currencies/dogecash" target='_blank'><img class="img-fluid" src="assets/images/logo-binance.png"></a>
                    <a href="https://mno.dogec.io" target='_blank'><img class="img-fluid" src="assets/images/logo-mno.svg"></a>
                    <a href="https://www.coingecko.com/en/coins/dogecash" target='_blank'><img class="img-fluid" src="assets/images/logo-coin-gecko.png"></a>
                    <a href="http://stex.dogec.io" target='_blank'><img class="img-fluid" src="assets/images/logo-stex.svg"></a>
                    <a href="https://new.capital/exchange/trade/DOGEC_BTC" target='_blank'><img class="img-fluid" src="assets/images/new-capital-logo.png"></a>
                </div>
            </div>
		</div>
	</div>
	<div id="block-b">
		<div class="container">
			<div class="row">
				<div class="col-12 text-center" data-aos="fade-up">
					<img class="img-fluid" src="assets/images/mac-wallet.png">
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Section 02 -->
<div id="section02" class="container-fluid">
    <!-- Block A -->
    <div class="block-a container" data-aos="fade-up">
        <div class="row">
            <div class="col-md-2">&nbsp;</div>
            <div class="col-12 col-md-8 text-center">
                <img src="assets/images/justdoge.svg" class="mb-3">
                <h2>Who says you can’t teach an old doge <span class="text-orange">new tricks?</span></h2>
                <p class="mt-5">DogeCash (DOGEC) is a transparent, community governed cryptocurrency aimed at preserving what makes DogeCoin unique while offering an alternative way for the average investor to get involved. This is done through the creation of DogeNodes, utilization of Proof of Stake, and active governance.</p>
                <p class="mt-5">We are actively developing an ecosystem of apps and platforms that will be mainly powered by DogeCash™ but will welcome many other major cryptocurrencies as well. The main goal? Generating profits to market buy DogeCash™ and then Airdrop a percentage of these coins to DogeNode owners and burn the remaining percentage on a monthly basis, thus decreasing supply, increasing our coin’s price and volume and giving additional bonus coins to all the heroes out there that help us secure our network by running a DogeNode.</p>
            </div>
            <div class="col-md-2">&nbsp;</div>
        </div>
    </div>
    <!-- Block B -->
    <div class="block-b row align-items-center">
        <div class="col-md-6 order-12 order-md-1" data-aos="fade-right">
            <div class="content">
	            <div  id="wallets">
                    <h2>A GREAT COIN DESERVES A <span class="text-orange">GREAT WALLET</span></h2>
                    <p class="mt-5">At DogeCash™ we focus on the small details. We have designed a wallet that its not only rock-solid in terms of stability but it is beautiful in every aspect of its UI. Now available for Download.</p>
                    <div id='OsDiv'>
                        <label for="OS">Select your operating system:</label>
                        <br>
                        <select id="OS" name="OS" onchange="OsSelect()">
                            <option value="SelectOS">Select Operating system</option>
                            <option value="Windows">Windows</option>
                            <option value="ARM">ARM</option>
                    	    <option value="Linux">Linux</option>
                    	    <option value="Mac">Mac</option>
                        </select>
                    </div>
                    <a id='download' href="" target="_blank" class="btn btn-orange mt-5" style='display:none;'></a>
                    <br><br>
                    Alternate Downloads at our <a href='https://api.github.com/repos/dogecash/dogecash/releases/latest'>Github</a>
                    <script>
                        var OS;
                        var bit = '64';
                        function OsSelect(){
                        	valsys = document.getElementById("OS").value;
                            if(valsys != 'SelectOS'){
                    			OS = valsys; 	
                                Download();
                            }
                        }
                        <?php
                        //Send a Get request to github and find the latest download version.
                        $url = "https://api.github.com/repos/dogecash/dogecash/releases/latest";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0" );
                        $response = curl_exec($ch);
                        curl_close($ch);
                         
                         
                         
                        //If $contents is not a boolean FALSE value.
                        if($response !== false){
                            //var_dump($response);
                            $response = json_decode($response, true);
                            $version = $response['tag_name'];
                            $versionWithoutV = substr($version, 1);
                        }
                        ?>
                        function Download(){
                    		if(OS =='Windows'){
                                var Download = document.getElementById('download');
                                $("#download").fadeIn();
                                Download.setAttribute('href', 'https://github.com/dogecash/dogecash/releases/download/<?php echo $version;?>/DogeCash-<?php echo $versionWithoutV;?>-win64-setup-unsigned.exe');
                                Download.innerHTML = OS + ' ' + ' Wallet Download';
                            }else if(OS =='Linux'){
                                var Download = document.getElementById('download');
                                Download.setAttribute('href', 'https://github.com/dogecash/dogecash/releases/download/<?php echo $version;?>/DogeCash-<?php echo $versionWithoutV;?>-x86_64-linux-gnu.tar.gz');
                                Download.innerHTML = OS + ' ' + ' Wallet Download';
                                $("#download").fadeIn();
                            }else if(OS =='Mac'){
                              var Download = document.getElementById('download');
                              Download.setAttribute('href', 'https://github.com/dogecash/dogecash/releases/download/<?php echo $version;?>/DogeCash-<?php echo $versionWithoutV;?>-osx-unsigned.dmg');
                              Download.innerHTML = OS + ' ' + ' Wallet Download';
                              $("#download").fadeIn();
                            }else if(OS =='ARM'){
                              var Download = document.getElementById('download');
                              Download.setAttribute('href', 'https://github.com/dogecash/dogecash/releases/download/<?php echo $version;?>/DogeCash-<?php echo $versionWithoutV;?>-arm-linux-gnueabihf.tar.gz');
                              Download.innerHTML = OS + ' ' + ' Wallet Download';
                              $("#download").fadeIn();
                            }
                        }
                    </script>
	            </div>
            </div>
        </div>
        <div class="col-md-6 order-1 order-md-12" data-aos="fade-left">
            <img src="assets/images/section02-block-b2.png" class="img-fluid">
        </div>
    </div>
    <!-- Block C -->
    <div class="block-d container mt-5" data-aos="fade-up">
        <div class="row">
            <div class="col-md-2">&nbsp;</div>
            <div class="col-12 col-md-8 text-center">
                <h2>It’s time to get the community <span class="text-orange">involved again!</span></h2>
                <p class="mt-5">No matter who you are, where you come from or what you do for a living, there is a place for you in our community and your voice will always be heard. After all, DogeCash™ belongs to the community and not just to a handful of people.</p>
                <p class="mt-5">DogeCash puts the future framework of the project in your hands via periodic voting on platform additions, expansions and partnerships. We do not just claim to be a “community project”, we embrace this ideal.</p>
                <h3 class="mt-5 text-cream">DogeCash walks the walk and barks the talk!</h3>
            </div>
            <div class="col-md-2">&nbsp;</div>
        </div>
    </div>
</div>

<div id="section03" class="container-fluid">

</div>

<!-- Section 04 -->
<div id="section04" class="container-fluid">
    <div class="container mb-5 pb-5">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="text-black"><u>DOGECASH™ SPECS</u></h2>
            </div>
        </div>
    </div>
	<div id="block-a" class="container">
		<div class="white-box-bg" data-aos="fade-up">
			<div class="row text-center text-black">
				<div class="col-12 col-md-4">
					<p>COIN NAME</p>
					<h2>DOGECASH</h2>
				</div>
				<div class="col-12 col-md-2">
					<p>COIN TICKER</p>
					<h2>DOGEC</h2>
				</div>
				<div class="col-12 col-md-auto">
					<p>TOTAL COINS</p>
					<h2>21MM</h2>
				</div>
				<div class="col-12 col-md-auto">
					<p>PREMINE</p>
					<h2>5%</h2>
				</div>
				<div class="col-12 col-md-auto">
					<p>NODE COLLATERAL</p>
					<h2>5,000</h2>
				</div>
			</div>
		</div>
	</div>
	<div id="block-b" class="container d-none d-md-block">
		<div class="row">
			<div class="col-12 col-md-6 mb-5 mb-md-0">
				<div class="white-box-bg" data-aos="fade-right">
					<div class="row">
						<div class="col-6 text-cream">ALGORITHM</div>
						<div class="col-6 text-black">QUARK</div>
						<div class="col-6 text-cream">BLOCK TIME</div>
						<div class="col-6 text-black">60 SECS</div>
						<div class="col-6 text-cream">REWARD DIST.</div>
						<div class="col-6 text-black">80% MN / 20% POS</div>
						<div class="col-6 text-cream">POW</div>
						<div class="col-6 text-black">NO</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6">
				<div class="white-box-bg" data-aos="fade-left">
					<div class="row">
						<div class="col-6 text-cream">DEV FEE</div>
						<div class="col-6 text-black">NO</div>
						<div class="col-6 text-cream">GOVERNANCE</div>
						<div class="col-6 text-black">YES</div>
						<div class="col-6 text-cream">NODE COLLATERAL</div>
						<div class="col-6 text-black">5,000 DOGEC</div>
						<div class="col-6 text-cream">STAKING MIN TIME</div>
						<div class="col-6 text-black">1 HOUR</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="block-c" class="container d-none d-md-block">
		<div class="white-box-bg" data-aos="fade-up">
			<div class="row text-center text-black">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col">BLOCKS</th>
								<th scope="col">BLOCK REWARD</th>
								<th scope="col">MN 80%</th>
								<th scope="col">POS 20%</th>
								<th scope="col">BUDGET 10% FROM BLOCK</th>
								<th scope="col">ROUND TIME FRAME</th>
								<th scope="col">OVERALL TIME</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th scope="row">PREMINE</th>
								<td>1</td>
								<td>1,050,000</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
							</tr>
							<tr>
								<th scope="row">POW/MAINNET</th>
								<td>2 - 1,440</td>
								<td>1</td>
								<td>0.8</td>
								<td>-</td>
								<td>-</td>
								<td>1 DAY</td>
								<td>1 DAY</td>
							</tr>
							<tr>
								<th scope="row">PRE SALE ROUND</th>
								<td>1,441 - 11,520</td>
								<td>10</td>
								<td>8</td>
								<td>2</td>
								<td>-</td>
								<td>7 DAYS</td>
								<td>8 DAYS</td>
							</tr>
							<tr>
								<th scope="row">EXCHANGE LAUNCH</th>
								<td>11,521 - 97,920</td>
								<td>16</td>
								<td>12.8</td>
								<td>3.2</td>
								<td>-</td>
								<td>60 DAYS</td>
								<td>68 DAYS</td>
							</tr>
							<tr>
								<th scope="row">PLATFORM 1 DEV</th>
								<td>97,921 - 184,320</td>
								<td>14</td>
								<td>11.2</td>
								<td>2.8</td>
								<td>-</td>
								<td>60 DAYS</td>
								<td>128 DAYS</td>
							</tr>
							<tr>
								<th scope="row">PLATFORM 2 DEV</th>
								<td>184,321 - 321,781</td>
								<td>12</td>
								<td>9.6</td>
								<td>2.4</td>
								<td>-</td>
								<td>95 DAYS</td>
								<td>223 DAYS</td>
							</tr>
							<tr>
								<th scope="row">PLATFORM 1 LIVE</th>
								<td>1 - 238,620</td>
								<td>12</td>
								<td>8.64</td>
								<td>2.16</td>
								<td>1.2</td>
								<td>165 DAYS</td>
								<td>389 DAYS</td>
							</tr>
							<tr>
								<th scope="row">PLATFORM 2 DEV</th>
								<td>238,620 - 764,221</td>
								<td>10</td>
								<td>7.2</td>
								<td>1.8</td>
								<td>1</td>
								<td>365 DAYS</td>
								<td>754 DAYS</td>
							</tr>

							<tr>
								<th scope="row">PLATFORMS LIVE</th>
								<td>764,221 - 4,445,940</td>
								<td>6.6</td>
								<td>5</td>
								<td>1</td>
								<td>0.6</td>
								<td>2555 DAYS</td>
								<td>3309 DAYS</td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div id="features">
		<div id="block-d" class="container">
			<div class="row">
				<div class="col-12 text-black text-center pb-5">
					<h2><u>DOGECASH™ FEATURES</u></h2>
				</div>
				<div class="col-12 grey-pattern-bg">
					<div class="items">
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature01.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Transparency</h4>
                                    <p class="text-black">Why is this the top doge on the list? Our team is sick and tired of scams and money grabs that rob people daily, that's why. Our team is comprised of trusted crypto experts whose identities aren't hidden. Additionally, talks are in the works to become the first <span style='color:#e0521a'><b>Know Your Developer (KYD) verified Presale.</b></span> If that isn't enough, the BTC Presale and DOGEC Premine addresses will be made public.</p>
                                    <p class="text-uppercase" style='color:#A25C08;'>We are keeping these doges on a tight leash!</p>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature02.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Community</h4>
                                    <p class="text-black">While a bit cliche,' the concept of "a coin is as strong as its community" led DogeCoin to becoming a $2 Billion Dollar project with worldwide recognition. Organic growth of our community is key to DogeCash and will be accomplished via team-community engagement, partnerships with existing projects and platform development.</p>
                                    <p class="text-uppercase" style='color:#A25C08;'>Did you miss the Dogecoin movement? Join the DogeCash revolution!</p>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature03.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Governance</h4>
                                    <p class="text-black">With DogeCash, DogeNode owners will vote on what the raised funds will be spent on. Want a DogeCash DEX for Masternode Only coins? You got it. How about a DogeBNB - an AirBNB for dog boarding?</p>
                                    <p class="text-uppercase" style='color:#A25C08;'>The future is in your paws!</p>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature04.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Scarcity</h4>
                                    <p class="text-black">Dogecoin went with the model of creating an abundant supply which put the coin in the hands of many people. However the continuously inflated supply reduces the value of DOGE. We are taking a different route and following the Bitcoin supply model - the total number of DogeCash coins <span style='color:#e0521a'><b>will never exceed 21 million.</b></span></p>
                                    <p class="text-uppercase" style='color:#A25C08;'>This limited supply model makes each DOGEC a rare doge.</p>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature05.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Charity</h4>
                                    <p class="text-black">Following in our sister coin's pawprints, giving back to charitable causes is part of the DogeCash mission. This is a community governed coin so you decide exactly where the funds will go.</p>
                                    <p class="text-uppercase" style='color:#A25C08;'>Send and receive addresses will always be made public.</p>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 mb-5 mb-md-0 d-flex justify-content-center"><img src="assets/images/feature06.png" class="img-fluid"></div>
                                <div class="col-12 col-md-6 text-center text-md-left">
                                    <h4 class="text-black">Quality</h4>
                                    <p class="text-black">If you haven't noticed already, DogeCash aims to bring you <span style='color:#e0521a'><b>Best in Show</b></span> delivery on every aspect of the project. This includes beautiful desktop wallets, sleek mobile apps, solid blockchain performance and innovative platforms.</p>
                                    <p class="text-uppercase" style='color:#A25C08;'>Attention to detail is our forte.</p>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Section 05 -->
<div id="whitepaper">
	<div id="section06" class="container-fluid">
		<div id="block-a" class="container-fluid">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-md-6 text-center text-md-left mb-5 mb-md-0" data-aos="fade-right">
						<h3 class="text-black">WHITEPAPER</h3>
						<p class="mt-5 text-black">We won't be insulting by telling you what a masternode is. Instead, read for a quick rundown of what our project is all about!</p>
						<p class="mt-5 mb-xl-5"><a class="btn btn-dark" href="http://whitepaper.dogec.io" target="_blank">DOWNLOAD <span class="text-orange">WHITEPAPER</span></a></p>
					</div>
					<div class="col-md-6 p-0 p-md-5">
						<img src="assets/images/section04-image.png" class="img-fluid d-md-none">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Section 06 -->
<div id="section07" class="container-fluid">&nbsp;</div>

<!-- Section 07 -->
<div id="section08">
	<div id="block-a" class="container-fluid">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-md-6 order-12 order-md-1" data-aos="fade-right"><img src="assets/images/section06-image.png" class="img-fluid"></div>
				<div class="col-md-6 order-1 order-md-12 text-center text-md-left" data-aos="fade-left">
					<h3 class="text-cream">MASTERNODE SETUP GUIDES</h3>
					<p class="mt-5">Instructions on how to setup DogeCash in masternode configuration with wallet integration.</p>
					<p class="mt-5 mb-5">
						<a href="https://mnguide.dogec.io/" class="btn btn-cream">COLD WALLET MASTERNODE GUIDE</a>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div id="roadmap" class="">
		<div class="row mb-5">
			<div class="col-12">
				<h2 class="text-center mb-5">ROADMAP</h2>
			</div>
		</div>
		<div class="" id="roadmap-content" data-aos="zoom-in-up">
			<div class="container">
				<div class="row">
				  <div class="col">
				    <div class="badge">Q4 2018</div>
				    <ul>
				      <li class="active">Mainnet Launch</li>
				      <li class="active">Governance UI Development</li>
				      <li class="active">Platform Vote #1</li>
				      <li class="active">Two Exchange Listings</li>
				    </ul>
				  </div>
				  <div class="col">
				    <div class="badge">Q1-Q3 2019</div>
				    <ul>
				      <li class="active">CoinMarketCap Listing</li>
				      <li class="active">Advertising Campaign</li>
				      <li class="active">Website Redesign</li>
				      <li class="active">Bounty Program Release</li>
				      <li class="active">Core Wallet Revamp</li>
				      <li class="active">SignalHub Platform #1 Development</li>
				      <li class="active">Prodoge Partnership</li>
				    </ul>
				  </div>
				  <div class="col">
				    <div class="badge">Q4 2019</div>
				    <ul>
				      <li class="active">Charity Vote and Donation #1</li>
				      <li class="active">Ecosystem and Platform Promotion</li>
				      <li class="active">HD Wallet Integration</li>
				    </ul>
				  </div>


				<div class="col">
				    <div class="badge">Q1 2020</div>
				    <ul>
				      <li class="">SignalHub Public Beta</li>
				      <li class="">Charity Vote and Donation #2</li>
				      <li class="">Ecosystem and Platform Promotion</li>
				      <li class="">Platform Vote #2</li>
				    </ul>
				  </div>


				<div class="col">
				    <div class="badge">Q2 2020</div>
				    <ul>
				      <li class="">Additional Exchange Listing</li>
				      <li class="">Platform #2 Development</li>
				      <li class="">Continue Ecosystem and Platform Promotion</li>
				    </ul>
				  </div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include('inc_footert.php'); ?>
<?php include('inc_foot.php'); ?>