</head>
<body>
    <!-- BEGIN Nav -->
    <nav class="navbar navbar-expand-md navbar-light fixed-top <?php echo $cfgNavClass; ?>">
        <div class="container">

            <a class="navbar-toggler" aria-expanded="false" aria-label="Toggle navigation"><img src="assets/images/hamburger.svg"></a>
            <a class="navbar-brand" href="index.html">
				<img alt="Dogecash Logo" src="assets/images/logo.svg" class="dark">
                <img alt="Dogecash Logo" src="assets/images/logo-doge-alt.svg" class="light">
            </a>

            <div class="collapse navbar-collapse" id="main-menu">

                <ul class="navbar-nav ml-auto mr-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link<?php echo navSel($strPage,'index'); ?>" href="/">
                            <span>home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo navSel($strPage,'features'); ?>" href="features/">features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#wallets" target="_blank">wallets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo navSel($strPage,'team'); ?>" href="team/">team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo navSel($strPage,'roadmap'); ?>" href="/#roadmap">roadmap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://explorer.dogec.io/" target="_blank">explorer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://blog.dogec.io/" target="_blank">blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://github.dogec.io" target="_blank">github</a>
                    </li>
                    <li class="nav-item" style='padding-right:15px;padding-left:15px;'>
                       <div id="google_translate_element"></div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- END Nav -->
