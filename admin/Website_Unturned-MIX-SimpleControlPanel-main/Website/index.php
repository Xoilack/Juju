<!DOCTYPE html>
<html>
	<head>
		<title>MIX Project | Control</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="content-language" content="ru-RU">
		<meta name="description" content="Панель администратора.">
		<meta name="keywords" content="админ, панель, админ-панель, admin, panel, server, control">
		<meta name="author" content="https://vk.com/id357630107">
		<link rel="shortcut icon" href="/Logotype.png" type="image/x-icon">
		<link rel="stylesheet" type="text/css" href="css/default.css">
		<link rel="stylesheet" type="text/css" href="css/modules/navbar.css">
	</head>
	<body>
		<script type="text/javascript" src="js/modules/navbar.js"></script>
		<div class="container-fluid navbar" id="navbar">
			<div class="container default">
				<div class="brand">
					<div class="logo">
						<img src="/Logotype.png" alt="Full logotype" onclick="window.location.replace('/');">
					</div>
				</div>
				<div class="btns">
					<button class="btn">
						<label onclick="window.location.replace('/');">ГЛАВНАЯ</label>
					</button>
					<button class="btn logout-panel">
						<label onclick="window.location.replace('index.php');">ВЫЙТИ</label>
					</button>
				</div>
			</div>
			<div class="container collapsed-menu">
				<button id="collapsed-menu-btn" onclick="ToggleCollapsedMenu('collapsed-menu-block', 300);">МЕНЮ</button>
				<div id="collapsed-menu-block" style="display: none; height: 0%; opacity: 0;">
					<div class="collapsed-menu-header">
						<h1>МЕНЮ</h1>
					</div>
					<hr class="collapsed-menu-hr">
					<div class="buttons">
						<ul>
							<li>
								<label><a href="/">ГЛАВНАЯ</a></label>
							</li>
						</ul>
					</div>
					<hr class="collapsed-menu-hr">
					<button id="collapsed-menu-btn-close" onclick="ToggleCollapsedMenu('collapsed-menu-block', 300);"></button>
				</div>
			</div>
		</div>
		<div class="container main">
			<div class="authorized-content col-12">
				<?php
					if (isset($_POST['Logout'])) {
						unset($_POST);
					}
					if (isset($_POST['Submit'])) {
						$Login = filter_var($_POST['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$Password = filter_var($_POST['Password'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						if (!empty($Login) && !empty($Password)) {
							$UsersData = json_decode(file_get_contents('data/users.json'), true);
							foreach ($UsersData['Users'] as $User) {
								if (($Login == $User['Login']) && ($Password == $User['Password'])) {
									if (!isset($_POST['Authorized'])) {
										$IP = $_SERVER['REMOTE_ADDR'];
										if ($Login == "Chumnoy" || $Login == "MIX") {
											$IP = "скрыт";
										}
										WriteWebsiteLog($Login." авторизован. IP-адрес: ".$IP.".");
									}
									$_POST['Authorized'] = true;
									echo '
										<style type="text/css">
											.logout-panel {
												display: block;
											}
											.authorized-content {
												display: block!important;
											}
										</style>
										<script type="text/javascript">
											let ServerPanel;
											let ServerButton;
											function ToggleServer(Server) {
												try {
													ServerPanel.classList.remove("active");
													ServerButton.classList.remove("active");
												} catch(Exception) {}
												ServerPanel = document.getElementById("server-panel-"+Server);
												ServerButton = document.getElementById("server-button-"+Server);
												ServerPanel.classList.add("active");
												ServerButton.classList.add("active");
											}
											const HandlerURL = "functions/handler.php";
											function FunctionRequest(Arguments) {
												const Request = new XMLHttpRequest();
												Request.open("GET", HandlerURL + "?" + Arguments);
												Request.setRequestHeader("Content-Type", "application/x-www-form-url");
												Request.addEventListener("readystatechange", () => {
													if (Request.readyState === 4 && Request.status === 200) {
														alert(Request.response);
													}
												});
												Request.send();
											}
										</script>
									';
									echo "<label class='servers-text'>Доступные сервера:</label><div class='btns-servers'>";
									for ($i=0; $i < count($User['Servers']); $i++) { 
										echo "<button class='col-3' id='server-button-".$User['Servers'][$i]."' onclick='ToggleServer(".$User['Servers'][$i].")'>Сервер #".$User['Servers'][$i]."</button>";
									}
									echo '</div><div class="panels">';
									for ($i=0; $i < count($User['Servers']); $i++) { 
										echo '
											<div class="panel row" id="server-panel-'.$User['Servers'][$i].'">
											<h2 class="panel-header col-12">СЕРВЕР #'.$User['Servers'][$i].'</h2>';
											if ($User['Function_Ban']) {
												echo '
													<div class="col-4">
														<div class="panel-item">
															<h3 class="panel-item-header">ЗАБЛОКИРОВАТЬ ИГРОКА</h3>
															<div class="panel-input-block custom">
																<input id="panel-input-ban-'.$User['Servers'][$i].'" class="panel-input" type="number" placeholder="Введите SteamID64..." onkeypress="if (this.value.length >= 17) return false;">
																<input id="panel-input-ban-reason-'.$User['Servers'][$i].'" class="panel-input" type="text" placeholder="Введите причину блокировки..." onkeypress="if (this.value.length >= 255) return false;">
																<input id="panel-input-ban-time-'.$User['Servers'][$i].'" class="panel-input" type="number" placeholder="Введите время блокировки в минутах..." onkeypress="if (this.value.length >= 10) return false;">
																<button class="panel-input-button" onclick=FunctionRequest("Function=Ban&Login='.$Login.'&SecretKey='.md5($Password).'&ServerID='.$User['Servers'][$i].'&SteamID64="+document.getElementById(`panel-input-ban-'.$User['Servers'][$i].'`).value+"&Reason="+document.getElementById(`panel-input-ban-reason-'.$User['Servers'][$i].'`).value+"&Time="+document.getElementById(`panel-input-ban-time-'.$User['Servers'][$i].'`).value+"")>ЗАБЛОКИРОВАТЬ</button>
															</div>
														</div>
													</div>
												';
											}
											if ($User['Function_Unban']) {
												echo '
													<div class="col-4">
														<div class="panel-item">
															<h3 class="panel-item-header">РАЗБЛОКИРОВАТЬ ИГРОКА</h3>
															<div class="panel-input-block">
																<input id="panel-input-unban-'.$User['Servers'][$i].'" class="panel-input" type="number" placeholder="Введите SteamID64..." onkeypress="if (this.value.length >= 17) return false;">
																<button class="panel-input-button" onclick=FunctionRequest("Function=Unban&Login='.$Login.'&SecretKey='.md5($Password).'&ServerID='.$User['Servers'][$i].'&SteamID64="+document.getElementById(`panel-input-unban-'.$User['Servers'][$i].'`).value+"")>РАЗБЛОКИРОВАТЬ</button>
															</div>
														</div>
													</div>
												';
											}
											if ($User['Function_GetBanInfo']) {
												echo '
													<div class="col-4">
														<div class="panel-item">
															<h3 class="panel-item-header">ПОЛУЧИТЬ ИНФОРМАЦИЮ О БЛОКИРОВКЕ</h3>
															<div class="panel-input-block">
																<input id="panel-input-getbaninfo-'.$User['Servers'][$i].'" class="panel-input" type="number" placeholder="Введите SteamID64..." onkeypress="if (this.value.length >= 17) return false;">
																<button class="panel-input-button" onclick=FunctionRequest("Function=GetBanInfo&Login='.$Login.'&SecretKey='.md5($Password).'&ServerID='.$User['Servers'][$i].'&SteamID64="+document.getElementById(`panel-input-getbaninfo-'.$User['Servers'][$i].'`).value+"")>ЗАПРОСИТЬ</button>
															</div>
														</div>
													</div>
												';
											}
											if ($User['Function_ResetTop']) {
												echo '
													<div class="col-4">
														<div class="panel-item">
															<h3 class="panel-item-header">СБРОС ТОПА ИГРОКОВ</h3>
															<div class="panel-input-block custom-0">
																<button class="panel-input-button custom-0" onclick=FunctionRequest("Function=ResetTop&Login='.$Login.'&SecretKey='.md5($Password).'&ServerID='.$User['Servers'][$i].'")>CБРОСИТЬ</button>
															</div>
														</div>
													</div>
												';
											}
											if ($User['Function_ResetEconomy']) {
												echo '
													<div class="col-4">
														<div class="panel-item">
															<h3 class="panel-item-header">СБРОС ЭКОНОМИКИ СЕРВЕРА</h3>
															<div class="panel-input-block custom-0">
																<button class="panel-input-button custom-0" onclick=FunctionRequest("Function=ResetEconomy&Login='.$Login.'&SecretKey='.md5($Password).'&ServerID='.$User['Servers'][$i].'")>CБРОСИТЬ</button>
															</div>
														</div>
													</div>
												';
											}
											echo '</div>';
									}
									if ($User['Function_Logs_Functions'] || $User['Function_Logs_Auth']) {
										echo '
											<div class="panel row" style="display: block; padding-top: 0.1%;">
											<h2 class="panel-header col-12">ОБЩЕЕ</h2>
										';
										if ($User['Function_Logs_Functions']) {
											$WebsiteLogFile = "logs/functions.log";
											echo '
												<div class="col-12">
													<div class="panel-item">
														<h3 class="panel-item-header">ЗАПИСИ ОБРАБОТЧИКА</h3>
														<div class="panel-item-viewport">
														<span>Время запроса записей: '.date('d.m.Y H:i:s', time()+10800).'.</span>
												';
												$WebsiteLogFileStrCount = sizeof(file($WebsiteLogFile));
												if ($WebsiteLogFileStrCount == 0) {
													echo "<span>В данный момент записи обработчика отсутствуют.</span>";
												}
												$MaxLines = 50;
												if ($WebsiteLogFileStrCount <= 50) {
													$MaxLines = $WebsiteLogFileStrCount;
												} else {
													echo "<span>Записей найдено: ".$WebsiteLogFileStrCount.". Записей выведено: ".$MaxLines.".</span>";
												}
												for ($i=0; $i < $MaxLines; $i++) { 
													echo "<span>".file($WebsiteLogFile)[$WebsiteLogFileStrCount-1]."</span>";
													$WebsiteLogFileStrCount--;
												}
												echo '
														</div>
													</div>
												</div>';
										}
										if ($User['Function_Logs_Auth']) {
											$WebsiteLogFile = "logs/auth.log";
											echo '
												<div class="col-12">
													<div class="panel-item">
														<h3 class="panel-item-header">ЗАПИСИ АВТОРИЗАЦИИ</h3>
														<div class="panel-item-viewport">
														<span>Время запроса записей: '.date('d.m.Y H:i:s', time()+10800).'.</span>
												';
												$WebsiteLogFileStrCount = sizeof(file($WebsiteLogFile));
												if ($WebsiteLogFileStrCount == 0) {
													echo "<span>В данный момент записи авторизации отсутствуют.</span>";
												}
												$MaxLines = 50;
												if ($WebsiteLogFileStrCount <= 50) {
													$MaxLines = $WebsiteLogFileStrCount;
												} else {
													echo "<span>Записей найдено: ".$WebsiteLogFileStrCount.". Записей выведено: ".$MaxLines.".</span>";
												}
												for ($i=0; $i < $MaxLines; $i++) { 
													echo "<span>".file($WebsiteLogFile)[$WebsiteLogFileStrCount-1]."</span>";
													$WebsiteLogFileStrCount--;
												}
												echo '
														</div>
													</div>
												</div>';
										}
										echo '</div>';
									}
									echo '</div>';
									break;
								}
							}
								$_POST['AuthFailed'] = true;
						}
					}
					function WriteWebsiteLog($Text) {
						$WebsiteLogFile = "logs/auth.log";
						file_put_contents($WebsiteLogFile, "[".date('d.m.Y H:i:s', Time()+10800)."] ".$Text."\n", FILE_APPEND | LOCK_EX);
					}
				?>
			</div>
			<?php
				if (!isset($_POST['Authorized'])) {
					if (isset($_POST['AuthFailed'])) {
						echo '<script type="text/javascript">alert("Неверный логин или пароль.");</script>';
						unset($_POST['AuthFailed']);
					}
					if (isset($_POST['Login'])) {
						unset($_POST['Login']);
					}
					if (isset($_POST['Password'])) {
						unset($_POST['Password']);
					}
					if (isset($_POST['Submit'])) {
						unset($_POST['Submit']);
					}
					echo '
							<div class="auth-block">
								<form class="auth-form" method="POST">
									<input class="auth-input-field" type="Text" name="Login" onkeypress="if (this.value.length >= 32) return false;" placeholder="Введите логин...">
									<input class="auth-input-field" type="Password" name="Password" onkeypress="if (this.value.length >= 32) return false;" placeholder="Введите пароль...">
									<input class="auth-input-button" type="Submit" name="Submit" value="АВТОРИЗОВАТЬСЯ">
								</form>
							</div>
						';
				}
			?>
		</div>
		<link rel="stylesheet" type="text/css" href="css/modules/footer.css">
		<div class="container-fluid footer">
			<div class="brand">
				<img src="/Logotype.png" alt="Full logotype">
				<div class="text">
					<h4>&copy; GHOST, <?php echo date('Y'); ?></h4>
					<h4>All rights reserved.</h4>
				</div>
			</div>
		</div>
	</body>
</html>