<?php
	if (isset($_GET)) {
		if (isset($_GET['Function'])) {
			$Function = filter_var($_GET['Function'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z]{1,15}$/')));
			$UsersData = json_decode(file_get_contents('../data/users.json'), true);
			$ServersData = json_decode(file_get_contents('../data/servers.json'), true);
			switch ($Function) {
				case 'Ban':
					if (isset($_GET['ServerID']) && isset($_GET['SteamID64']) && isset($_GET['Login']) && isset($_GET['SecretKey']) && isset($_GET['Time'])) {
						$ServerID = filter_var($_GET['ServerID'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,8}$/')));
						$SteamID64 = filter_var($_GET['SteamID64'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{17}$/')));
						$Login = filter_var($_GET['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$SecretKey = filter_var($_GET['SecretKey'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{32}$/')));
						$Reason = $_GET['Reason'];
						$Time = filter_var($_GET['Time'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,10}$/')));
						if (!empty($ServerID) && !empty($Login) && !empty($SecretKey)) {
							foreach ($UsersData['Users'] as $User) {
								if ($Login == $User['Login']) {
									if ($SecretKey == md5($User['Password'])) {
										if ($User['Function_Ban']) {
											if (in_array($ServerID, $User['Servers'])) {
												if (empty($SteamID64)) {
													echo "SteamID64 имеет неверный формат.";
													exit();
												}
												if (empty($Time)) {
													echo "Время блокировки указано некорректно.";
													exit();
												}
												if (preg_match('/"/', $Reason) || preg_match("/'/", $Reason) || preg_match('/`/', $Reason)) {
													echo 'При описании причины блокировки используются запрещённые символы.';
													exit();
												}
												if (strlen($Reason) > 64) {
													echo 'Причины блокировки не может быть длиннее 64 символов.';
													exit();
												}
												if (empty($Reason)) {
													$Reason = "Причина не указана.";
												}
												$conn = mysqli_connect($ServersData[$ServerID]['DBAddress'], $ServersData[$ServerID]['DBUsername'], $ServersData[$ServerID]['DBPassword']);
												if ($conn) {
													mysqli_select_db($conn, $ServersData[$ServerID]['DBName']);
													if ($conn->query("INSERT INTO `banlist` (`DisplayName`, `SteamID64`, `IP`, `Minutes`, `AdminName`, `Reason`, `TimeAdd`) VALUES ('NULL', '".$SteamID64."', 'NULL', '".$Time."', '[AdminPanel] ".$Login."', '".$Reason."', '".date('d.m.Y H:i:s', Time()+10800)."')")) {
														echo 'Игрок "'.$SteamID64.'" заблокирован на '.$Time.' мин.';
														WriteWebsiteLog("[Server #".$ServerID."] [Ban] ".$Login." заблокировал '".$SteamID64."' на ".$Time." мин. (до ".date('d.m.Y H:i:s', Time()+10800).") по причине: '".$Reason."'.");
													} else echo "Не удалось заблокировать игрока.";
												} else echo "Нет доступа к базе данных.";
											} else echo "Сервер, к которому применена функция не доступен.";
										} else {
											echo "Нет доступа к функции.";
										}
									} else echo "Пользователь не авторизован.";
									exit();
								}
							}
							echo "Пользователь не найден.";
						} else echo "Важный аргумент, переданный обработчику, указан не корректно.";
					} else echo "Для выполнения функции обработчику не достаточно аргументов.";
					break;
				case 'Unban':
					if (isset($_GET['ServerID']) && isset($_GET['SteamID64']) && isset($_GET['Login']) && isset($_GET['SecretKey'])) {
						$ServerID = filter_var($_GET['ServerID'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,8}$/')));
						$SteamID64 = filter_var($_GET['SteamID64'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{17}$/')));
						$Login = filter_var($_GET['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$SecretKey = filter_var($_GET['SecretKey'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{32}$/')));
						if (!empty($ServerID) && !empty($Login) && !empty($SecretKey)) {
							foreach ($UsersData['Users'] as $User) {
								if ($Login == $User['Login']) {
									if ($SecretKey == md5($User['Password'])) {
										if ($User['Function_Ban']) {
											if (in_array($ServerID, $User['Servers'])) {
												if (empty($SteamID64)) {
													echo "SteamID64 имеет неверный формат.";
													exit();
												}
												$conn = mysqli_connect($ServersData[$ServerID]['DBAddress'], $ServersData[$ServerID]['DBUsername'], $ServersData[$ServerID]['DBPassword']);
												if ($conn) {
													mysqli_select_db($conn, $ServersData[$ServerID]['DBName']);
													if (mysqli_fetch_row($conn->query("SELECT COUNT(*) FROM `banlist` WHERE `SteamID64`='".$SteamID64."'"))[0] >= 1) {
														if ($conn->query("DELETE FROM `banlist` WHERE `SteamID64`='".$SteamID64."'")) {
															echo "Блокировка успешно снята.";
														WriteWebsiteLog("[Server #".$ServerID."] [Unban] ".$Login." разблокировал '".$SteamID64."'.");
														} else echo "Не удалось снять блокировку.";
													} else echo "Игрок не имеет блокировки.";
												} else echo "Нет доступа к базе данных.";
											} else echo "Сервер, к которому применена функция не доступен.";
										} else {
											echo "Нет доступа к функции.";
										}
									} else echo "Пользователь не авторизован.";
									exit();
								}
							}
							echo "Пользователь не найден.";
						} else echo "Важный аргумент, переданный обработчику, указан не корректно.";
					} else echo "Для выполнения функции обработчику не достаточно аргументов.";
					break;
				case 'GetBanInfo':
					if (isset($_GET['ServerID']) && isset($_GET['SteamID64']) && isset($_GET['Login']) && isset($_GET['SecretKey'])) {
						$ServerID = filter_var($_GET['ServerID'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,8}$/')));
						$SteamID64 = filter_var($_GET['SteamID64'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{17}$/')));
						$Login = filter_var($_GET['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$SecretKey = filter_var($_GET['SecretKey'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{32}$/')));
						if (!empty($ServerID) && !empty($Login) && !empty($SecretKey)) {
							foreach ($UsersData['Users'] as $User) {
								if ($Login == $User['Login']) {
									if ($SecretKey == md5($User['Password'])) {
										if ($User['Function_GetBanInfo']) {
											if (in_array($ServerID, $User['Servers'])) {
												if (empty($SteamID64)) {
													echo "SteamID64 имеет неверный формат.";
													exit();
												}
												$conn = mysqli_connect($ServersData[$ServerID]['DBAddress'], $ServersData[$ServerID]['DBUsername'], $ServersData[$ServerID]['DBPassword']);
												if ($conn) {
													mysqli_select_db($conn, $ServersData[$ServerID]['DBName']);
													if (mysqli_fetch_row($conn->query("SELECT COUNT(*) FROM `banlist` WHERE `SteamID64`='".$SteamID64."'"))[0] >= 1) {
														if ($info = mysqli_fetch_row($conn->query("SELECT * FROM `banlist` WHERE `SteamID64`='".$SteamID64."'"))) {
															echo "Игрок: ".$info[1]."\nIP-адрес: ".$info[3]."\nПричина: ".$info[6]."\nЗаблокировал: ".$info[5]."\nЗаблокирован до: ".date('H:i:s d/m/Y', (strtotime($info[7])+$info[4]*60))."\nСтатус блокировки: ";
															if (time()+10800 <= strtotime($info[7])+$info[4]*60) {
																echo "Активна";
															} else echo "Истекла";
															WriteWebsiteLog("[Server #".$ServerID."] [GetBanInfo] ".$Login." заросил информацию о блокировке игрока '".$SteamID64."'.");
														} else echo "Не удалось получить информацию о блокировке.";
													} else echo "Игрок не имеет блокировки.";
												} else echo "Нет доступа к базе данных.";
											} else echo "Сервер, к которому применена функция не доступен.";
										} else {
											echo "Нет доступа к функции.";
										}
									} else echo "Пользователь не авторизован.";
									exit();
								}
							}
							echo "Пользователь не найден.";
						} else echo "Важный аргумент, переданный обработчику, указан не корректно.";
					} else echo "Для выполнения функции обработчику не достаточно аргументов.";
					break;
				case 'ResetTop':
					if (isset($_GET['ServerID']) && isset($_GET['Login']) && isset($_GET['SecretKey'])) {
						$ServerID = filter_var($_GET['ServerID'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,8}$/')));
						$Login = filter_var($_GET['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$SecretKey = filter_var($_GET['SecretKey'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{32}$/')));
						if (!empty($ServerID) && !empty($Login) && !empty($SecretKey)) {
							foreach ($UsersData['Users'] as $User) {
								if ($Login == $User['Login']) {
									if ($SecretKey == md5($User['Password'])) {
										if ($User['Function_ResetTop']) {
											if (in_array($ServerID, $User['Servers'])) {
												$conn = mysqli_connect($ServersData[$ServerID]['DBAddress'], $ServersData[$ServerID]['DBUsername'], $ServersData[$ServerID]['DBPassword']);
												if ($conn) {
													mysqli_select_db($conn, $ServersData[$ServerID]['DBName']);
													if ($conn->query("TRUNCATE TABLE `arum_tops`")) {
														echo "Топ игроков успешно сброшен.";
														WriteWebsiteLog("[Server #".$ServerID."] [ResetTop] ".$Login." сбросил топ игроков.");
													} else echo "Не удалось сбросить топ игроков.";
												} else echo "Нет доступа к базе данных.";
											} else echo "Сервер, к которому применена функция не доступен.";
										} else {
											echo "Нет доступа к функции.";
										}
									} else echo "Пользователь не авторизован.";
									exit();
								}
							}
							echo "Пользователь не найден.";
						} else echo "Важный аргумент, переданный обработчику, указан не корректно.";
					} else echo "Для выполнения функции обработчику не достаточно аргументов.";
					break;
				case 'ResetEconomy':
					if (isset($_GET['ServerID']) && isset($_GET['Login']) && isset($_GET['SecretKey'])) {
						$ServerID = filter_var($_GET['ServerID'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[0-9]{1,8}$/')));
						$Login = filter_var($_GET['Login'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{1,32}$/')));
						$SecretKey = filter_var($_GET['SecretKey'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>'/^[a-zA-Z0-9]{32}$/')));
						if (!empty($ServerID) && !empty($Login) && !empty($SecretKey)) {
							foreach ($UsersData['Users'] as $User) {
								if ($Login == $User['Login']) {
									if ($SecretKey == md5($User['Password'])) {
										if ($User['Function_ResetEconomy']) {
											if (in_array($ServerID, $User['Servers'])) {
												$conn = mysqli_connect($ServersData[$ServerID]['DBAddress'], $ServersData[$ServerID]['DBUsername'], $ServersData[$ServerID]['DBPassword']);
												if ($conn) {
													mysqli_select_db($conn, $ServersData[$ServerID]['DBName']);
													if ($conn->query("TRUNCATE TABLE `".$ServerID."_arum_economy_players`")) {
														echo "Экономика успешно сброшена.";
														WriteWebsiteLog("[".date('d.m.Y H:i:s', Time()+10800)."] [Server #".$ServerID."] [ResetTop] ".$Login." сбросил экономику.");
													} else echo "Не удалось сбросить экономику.";
												} else echo "Нет доступа к базе данных.";
											} else echo "Сервер, к которому применена функция не доступен.";
										} else {
											echo "Нет доступа к функции.";
										}
									} else echo "Пользователь не авторизован.";
									exit();
								}
							}
							echo "Пользователь не найден.";
						} else echo "Важный аргумент, переданный обработчику, указан не корректно.";
					} else echo "Для выполнения функции обработчику не достаточно аргументов.";
					break;
				default:
					echo "Неизвестная для обработчика функция.";
					if (empty($Login)) {
						$Login = "NULL";
					}
					WriteWebsiteLog("[Server #".$ServerID."] [Unknown] ".$Login." (".$_SERVER['REMOTE_ADDR'].") вызвал неизвестную функцию '".$Function."'.");
					break;
			}
		} else echo "В GET запросе не указана функция для обработчика.";
		WriteWebsiteLog("[Guard] Произведена попытка использования обработчика без указания функции с IP-адреса: ".$_SERVER['REMOTE_ADDR'].".");
	} else {
		echo "Обработчик не обнаружил GET запрос.";
		WriteWebsiteLog("[Guard] Произведена попытка использования обработчика без GET-запроса с IP-адреса: ".$_SERVER['REMOTE_ADDR'].".");
	}
	function WriteWebsiteLog($Text) {
		$WebsiteLogFile = "../logs/functions.log";
		file_put_contents($WebsiteLogFile, "[".date('d.m.Y H:i:s', Time()+10800)."] ".$Text."\n", FILE_APPEND | LOCK_EX);
	}
?>