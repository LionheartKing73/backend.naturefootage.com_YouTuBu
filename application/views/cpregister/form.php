<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo $title; ?></title>
	<link href="/data/css/bootstrap.css" media="all" rel="stylesheet" type="text/css" />
	<script src="/data/js/jquery-1.9.1.min.js"></script>
</head>
<body>

<style type="text/css">
	body {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		width: 960px;
		margin: 0 auto;
		font-size: 12px;
		padding: 20px;
		color: #474747;
		background: #eeeff0 url( "/data/img/body_bg.jpg" ) no-repeat;
	}
	.clear {
		clear: both;
	}
	#page {
		width: 938px;
		height: auto;
		border: 1px solid #c3c3c3;
		padding: 10px;
		margin-top: 20px;
		background: #f9f9f9;
		border-radius: 10px;
		opacity: .8;
	}
	#page h2 {
		display: block;
		text-align: center;
		color: #464646;
		font-size: 21px;
		line-height: 40px;
		font-weight: normal;
		margin: 0;
		text-shadow: 0 1px 0 #fff;
	}
	#form-block {
		float: left;
		width: 500px;
		border-right: 1px solid #c3c3c3 ;
		margin: 20px 0 10px 0;
	}
	#desc-block {
		margin: 20px 0;
		float: right;
		width: 436px;
		height: 100%;
	}
	#form-block ul {
		list-style: none;
	}
	#form-block ul li {
		padding: 10px 0;
	}
	#form-block input[name="register"] {
		float: right;
		height: 28px;
		width: 132px;
		background: #9bc550;
		border: 1px solid #7FA240;
		border-radius: 4px;
		font-size: 14px;
		color: #fff;
		text-align: center;
		bottom: 5px;
		position: relative;
		padding-left: 8px !important;
		margin: 10px 21px 0 0 !important;
		background-image: linear-gradient( top, #9bc550 0%, #8EB449 80%);
		background-image: -o-linear-gradient( top, #9bc550 0%, #8EB449 80%);
		background-image: -moz-linear-gradient( top, #9bc550 0%, #8EB449 80%);
		background-image: -webkit-linear-gradient( top, #9bc550 0%, #8EB449 80%);
		background-image: -ms-linear-gradient( top, #9bc550 0%, #8EB449 80%);
	}
	#form-block input[name="register"]:hover {
		background-image: linear-gradient( bottom, #9bc550 0%, #8EB449 50%);
		background-image: -o-linear-gradient( bottom, #9bc550 0%, #8EB449 50%);
		background-image: -moz-linear-gradient( bottom, #9bc550 0%, #8EB449 50%);
		background-image: -webkit-linear-gradient( bottom, #9bc550 0%, #8EB449 50%);
		background-image: -ms-linear-gradient( bottom, #9bc550 0%, #8EB449 50%);
	}
	#form-block label[for^="form-"] {
		display: inline-block;
		width: 210px;
		height: 28px;
		text-align: right;
		margin-bottom: 9px;
		margin: 0 10px;
		font-weight: bold;
	}
	#form-block input[id^="form-"] {
		display: inline-block;
		margin: 0;
	}
	.error-message {
		display: none;
		display: block;
		text-align: right;
		color: #ff0000;
		font-size: 11px;
		padding-right: 20px;
	}
</style>
<script type="text/javascript">
	$( document ).ready( function () {
		var $errors = <?php echo ( $errors_json ) ? $errors_json : '[]'; ?>;
		//alert( $errors );
		for ( var $i = 0; !! $errors[ $i ]; $i++ ) {
			var $code = $errors[ $i ].code;
			var $element = $errors[ $i ].element;
			//alert( $element + ' ' + $code );
			SetErrorMessage( $element, $code );
		}
	} );
	function SetErrorMessage ( $element, $code ) {
		var $jQelement = $( '.unit-' + $element );
		var $message = '<span class="error-message">' + CreateErrorMessage( $code, $element ) + '</span>';
		var $html = $jQelement.html();
		$jQelement.html( $html + $message );
	}
	function CreateErrorMessage ( $code, $name ) {
		var $fieldname = '';
		for ( var $i = 0; $i < $name.length; $i++ ) {
			$fieldname += ( $i == 0 ) ? $name[ $i ].toUpperCase() : $name[ $i ];
		}
		switch ( $code ) {
			case 'empty-field':
				return 'Required field "' + $fieldname + '"';
			case 'illegal-characters':
				return 'Illegal charasters in field "' + $fieldname + '"';
			case 'wrong-email':
				return 'Wrong email address';
            case 'wrong-sitedomain':
                return 'Wrong site domain';
			case 'minimum-charasters':
				return 'Need at least five characters';
			case 'email-busy':
				return 'This email is busy';
			case 'login-busy':
				return 'This login is busy';
			case 'sitename-busy':
				return 'This sitename is busy';
			case 'sitedomain-busy':
				return 'This domain is busy';
		}
		return null;
	}
</script>

	<div id="page">

		<h2>Content Provider registration:</h2>
		<div id="form-block">

			<form method="POST" action="/<?php echo $lang; ?>/cpregister/">

				<ul>
					<li id="message">
						<?php echo $message; ?>
					</li>
					<li class="unit-login">
						<label for="form-login">
							<span>Login :</span>
						</label>
						<input type="text" name="login" id="form-login" value="<?php echo $form[ 'login' ]; ?>" autocomplete="off" />
					</li>
					<li class="unit-name">
						<label for="form-name">
							<span>Name :</span>
						</label>
						<input type="text" name="name" id="form-name" value="<?php echo $form[ 'name' ]; ?>" autocomplete="off" />
					</li>
					<li class="unit-surname">
						<label for="form-surname">
							<span>Surname :</span>
						</label>
						<input type="text" name="surname" id="form-surname" value="<?php echo $form[ 'surname' ]; ?>" autocomplete="off" />
					</li>
					<li class="unit-email">
						<label for="form-email">
							<span>Email :</span>
						</label>
						<input type="text" name="email" id="form-email" value="<?php echo $form[ 'email' ]; ?>" autocomplete="off" />
					</li>
					<li class="unit-sitename">
						<label for="form-sitename">
							<span>Site name :</span>
						</label>
						<input type="text" name="sitename" id="form-sitename" value="<?php echo $form[ 'sitename' ]; ?>" autocomplete="off" />
					</li>
                    <li class="unit-sitedomain">
                        <label for="form-sitedomain">
                            <span>Site domain :</span>
                        </label>
                        <input type="text" name="sitedomain" id="form-sitedomain" value="<?php echo $form[ 'sitedomain' ]; ?>" autocomplete="off" />
                    </li>
				</ul>

				<input type="submit" name="register" id="form-submit" value="Register" />

			</form>

		</div>

		<div id="desc-block">
			<center>...</center>
		</div>

		<div class="clear"></div>

	</div>

</body>
</html>