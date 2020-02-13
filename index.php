<?php
// Settings
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "freelancehunt";
$token = "";
$project_category = '1,86,99';
// End Settings


// Connect to MySQL
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// id progect_id date progect_name progect_link, progect_price, user_name, user_login, user_skills
$create_table = "CREATE TABLE `$dbName`.`db_project` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `project_id` INT(11) NOT NULL , `date` DATETIME NOT NULL , `project_name` VARCHAR(255) NOT NULL , `project_link` VARCHAR(255) NOT NULL , `project_price` INT(11) NULL DEFAULT NULL , `user_name` VARCHAR(24) NOT NULL , `user_login` VARCHAR(24) NOT NULL , `user_skills` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB";
// If database is not exist create one
if (!mysqli_select_db($conn,$dbName)){
    $sql = "CREATE DATABASE ".$dbName;
    if ($conn->query($sql) === TRUE) {
		$conn->query($create_table);
        // echo "Database created successfully";
    } else {
        // echo "Error creating database: " . $conn->error;
    }
}


//setup the request, you can also use CURLOPT_URL
$url = 'https://api.freelancehunt.com/v2/projects?filter[skill_id]=' . $project_category;

if (!$ch = curl_init()) {
    echo curl_error($ch);
    exit;
}
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Authorization: Bearer ' . $token
   ));
curl_setopt($ch, CURLOPT_URL, $url);  
$data = curl_exec($ch);
$result = json_decode($data, true);

$count_all_page = substr($result[links][last], strrpos($result[links][last], '=') + 1);

$sql = "SELECT project_id FROM db_project WHERE date LIKE '%" . date("Y-m-d") . "%'";
$result = $conn->query($sql);
if (!$result->num_rows) {
	
	$sql = "SELECT * FROM db_project";
	$arr_project_id = [];
	if ($result_id = $conn->query($sql)) {
		while($row = $result_id->fetch_array()) {
			$arr_project_id[] = $row[project_id];
		}
	}
	// print_r ($arr_project_id);

	for ($i = $count_all_page; $i > 0; $i--) {
		$url = 'https://api.freelancehunt.com/v2/projects?filter[skill_id]=1,86,99&page[number]=' . $i;
		curl_setopt($ch, CURLOPT_URL, $url);  
		$data = curl_exec($ch);
		$result = json_decode($data, true);
		
		foreach ($result[data] as $val){
			if (in_array($val[id], $arr_project_id)) {
				continue;
			}
			$data = [];
			$data[project_id] = $val[id];
			$data[date] = date('Y-m-d H:i:s');
			$data[project_name] = $val[attributes][name];
			$data[project_link] = $val[links][self][web];
			
			if ($val[attributes][budget][currency] == 'RUB') {
				$kurs = @file_get_contents('https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');
				$decode_kurs = json_decode($kurs,true);
				$data[project_price] = round($val[attributes][budget][amount] * $decode_kurs[2][sale], 0);
			} elseif ($val[attributes][budget][currency] == 'UAH') {
				$data[project_price] = $val[attributes][budget][amount];
			} else {
				$data[project_price] = '0';
			}
			
			$data[user_name] = $val[attributes][employer][first_name];
			$data[user_login] = $val[attributes][employer][login];
			
			$all_skills = [];
			foreach ( $val[attributes][skills] as $skill ){
				$all_skills[] = $skill[id];
			}
			$data[user_skills] = implode(",", $all_skills);
			/*
			echo '<pre>';
			print_r ($data);
			echo '</pre><br><br>';
			*/
			$insert_data = "INSERT INTO `db_project` (`project_id`, `date`, `project_name`, `project_link`, `project_price`, `user_name`, `user_login`, `user_skills`) 
			VALUES ('" . $data[project_id] . "', '" . $data[date] . "', '" . $data[project_name] . "', '" . $data[project_link] . "', '" . $data[project_price] . "', '" . $data[user_name] . "', '" . $data[user_login] . "', '" . $data[user_skills] . "')";

			$conn->query($insert_data) or dir ($conn->error);
		}
	}
}

curl_close($ch); // Close curl






$all_skils = [
'56' => '1C',
'182' => 'Blockchain',
'24' => 'C#',
'2' => 'C/C++',
'177' => 'Delphi/Object Pascal',
'5' => 'Flash/Flex',
'173' => 'Go',
'13' => 'Java',
'28' => 'Javascript',
'146' => 'Mac OS/Objective C',
'61' => 'Microsoft .NET',
'174' => 'Node.js',
'1' => 'PHP',
'22' => 'Python',
'23' => 'Ruby',
'160' => 'Swift',
'86' => 'Базы данных',
'99' => 'Веб-программирование',
'176' => 'Встраиваемые системы и микроконтроллеры',
'65' => 'Защита ПО и безопасность',
'175' => 'Машинное обучение',
'169' => 'Парсинг данных',
'103' => 'Прикладное программирование',
'180' => 'Разработка ботов',
'88' => 'Разработка игр',
'85' => 'Системное программирование',
'57' => 'Тестирование и QA',
'59' => '3D графика',
'41' => 'Баннеры',
'58' => 'Векторная графика',
'111' => 'Визуализация и моделирование',
'156' => 'Дизайн визиток',
'132' => 'Дизайн выставочных стендов',
'42' => 'Дизайн интерфейсов',
'106' => 'Дизайн интерьеров',
'179' => 'Дизайн мобильных приложений',
'43' => 'Дизайн сайтов',
'117' => 'Дизайн упаковки',
'141' => 'Живопись и графика',
'93' => 'Иконки и пиксельная графика',
'90' => 'Иллюстрации и рисунки',
'172' => 'Инфографика',
'17' => 'Логотипы',
'109' => 'Наружная реклама',
'18' => 'Обработка фото',
'151' => 'Оформление страниц в социальных сетях',
'75' => 'Полиграфический дизайн',
'164' => 'Предметный дизайн',
'152' => 'Разработка шрифтов',
'77' => 'Фирменный стиль',
'124' => 'HTML/CSS верстка',
'161' => 'Видеосъемка',
'129' => 'Интеграция платежных систем',
'68' => 'Интернет-магазины и электронная коммерция',
'104' => 'Контент-менеджер',
'94' => 'Маркетинговые исследования',
'83' => 'Настройка ПО/серверов',
'178' => 'Обработка данных',
'95' => 'Обучение',
'170' => 'Поиск и сбор информации',
'165' => 'Прототипирование',
'171' => 'Работа с клиентами',
'114' => 'Разработка презентаций',
'142' => 'Рукоделие/Hand made',
'96' => 'Создание сайта под ключ',
'45' => 'Сопровождение сайтов',
'78' => 'Установка и настройка CMS',
'139' => 'Фотосъемка',
'91' => 'Анимация',
'113' => 'Аудио/видео монтаж',
'144' => 'Видеореклама',
'100' => 'Музыка',
'102' => 'Обработка аудио',
'101' => 'Обработка видео',
'122' => 'Транскрибация',
'143' => 'Услуги диктора',
'136' => 'E-mail маркетинг',
'134' => 'SEO-аудит сайтов',
'127' => 'Контекстная реклама',
'14' => 'Поисковое продвижение (SEO)',
'135' => 'Поисковое управление репутацией (SERM)',
'184' => 'Покупка ссылок',
'162' => 'Продажи и генерация лидов',
'131' => 'Продвижение в социальных сетях (SMM)',
'133' => 'Реклама в социальных медиа',
'145' => 'Тизерная реклама',
'108' => 'Архитектурные проекты',
'148' => 'Инжиниринг',
'107' => 'Ландшафтный дизайн',
'64' => 'Проектирование',
'147' => 'Чертежи и схемы',
'183' => 'Гибридные мобильные приложения',
'121' => 'Разработка под Android',
'120' => 'Разработка под iOS (iPhone/iPad)',
'181' => 'DevOps',
'62' => 'IP-телефония/VoIP',
'6' => 'Linux/Unix',
'7' => 'Windows',
'39' => 'Администрирование систем',
'115' => 'Геоинформационные системы',
'72' => 'Компьютерные сети',
'112' => 'Бизнес-консультирование',
'149' => 'Бухгалтерские услуги',
'154' => 'Консалтинг',
'159' => 'Рекрутинг',
'150' => 'Управление клиентами/CRM',
'89' => 'Управление проектами',
'153' => 'Юридические услуги',
'79' => 'Английский язык',
'166' => 'Иврит',
'84' => 'Испанский язык',
'82' => 'Итальянский язык',
'157' => 'Локализация ПО, сайтов и игр',
'80' => 'Немецкий язык',
'37' => 'Перевод текстов',
'158' => 'Французский язык',
'76' => 'Копирайтинг',
'38' => 'Написание статей',
'163' => 'Написание сценария',
'123' => 'Нейминг и слоганы',
'138' => 'Публикация объявлений',
'168' => 'Редактура и корректура текстов',
'125' => 'Рерайтинг',
'116' => 'Рефераты, дипломы, курсовые',
'140' => 'Стихи, песни, проза',
'97' => 'Техническая документация'
];


$total_pages = $conn->query('SELECT * FROM db_project')->num_rows;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

$num_results_on_page = 10;

if ($stmt = $conn->prepare('SELECT * FROM db_project ORDER BY id DESC LIMIT ?,?')) {
	
	$calc_page = ($page - 1) * $num_results_on_page;
	$stmt->bind_param('ii', $calc_page, $num_results_on_page);
	$stmt->execute();
	$result = $stmt->get_result();	
	
	$sql0 = "SELECT id FROM db_project WHERE project_price = '';";
	$sql500 = "SELECT id FROM db_project WHERE project_price > 0 AND project_price < 501;";
	$sql1000 = "SELECT id FROM db_project WHERE project_price > 500 AND project_price < 1001;";
	$sql5000 = "SELECT id FROM db_project WHERE project_price > 1000 AND project_price < 5001;";
	$sql10000 = "SELECT id FROM db_project WHERE project_price > 5000 AND project_price < 10001;";
	$sql100000 = "SELECT id FROM db_project WHERE project_price > 10000 ;";
	
	$result0 = mysqli_num_rows(mysqli_query($conn, $sql0));
	$result500 = mysqli_num_rows(mysqli_query($conn, $sql500));
	$result1000 = mysqli_num_rows(mysqli_query($conn, $sql1000));
	$result5000 = mysqli_num_rows(mysqli_query($conn, $sql5000));
	$result10000 = mysqli_num_rows(mysqli_query($conn, $sql10000));
	$result100000 = mysqli_num_rows(mysqli_query($conn, $sql100000));
	
	$result_summ = $result0 + $result500 + $result1000 + $result5000 + $result10000 + $result100000;
	
	$dataPoints = array( 
				array("label"=>"Не указан бюджет", "y"=>round($result0/$result_summ*100,2)),
				array("label"=>"От 1 до 500", "y"=>round($result500/$result_summ*100,2)),
				array("label"=>"От 500 до 1000", "y"=>round($result1000/$result_summ*100,2)),
				array("label"=>"От 1000 до 5000", "y"=>round($result5000/$result_summ*100,2)),
				array("label"=>"От 5000 до 10000", "y"=>round($result10000/$result_summ*100,2)),
				array("label"=>"Больше 10000", "y"=>round($result100000/$result_summ*100,2))
			);

	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="UTF-8" />
			<title>Freelancehunt</title>
			<style>
				html {
					font-family: Tahoma, Geneva, sans-serif;
					padding: 20px;
					background-color: #F8F9F9;
				}
				.container {
					width: 100%;
					max-width: 1170px;
					margin-right: auto;
					margin-left: auto;
				}
				div.table {
					text-align: center;
				}
				table {
					border-collapse: collapse;
					margin-right: auto;
					margin-left: auto;
				}
				tr > td:nth-child(1) {
					text-align: left;
					width:50%
				}
				td, th {
					padding: 10px;
				}
				th {
					background-color: #54585d;
					color: #ffffff;
					font-weight: bold;
					font-size: 13px;
					border: 1px solid #54585d;
				}
				td {
					color: #636363;
					border: 1px solid #dddfe1;
				}
				tr {
					background-color: #ffffff;
				}
				/*
				tr:nth-child(odd) {
					background-color: #ffffff;
				}
				*/
				.pagination {
					list-style-type: none;
					padding: 10px 0;
					display: inline-flex;
					justify-content: space-between;
					box-sizing: border-box;
				}
				.pagination li {
					box-sizing: border-box;
					padding-right: 10px;
				}
				.pagination li a {
					box-sizing: border-box;
					background-color: #e2e6e6;
					padding: 8px;
					text-decoration: none;
					font-size: 12px;
					font-weight: bold;
					color: #616872;
					border-radius: 4px;
				}
				.pagination li a:hover {
					background-color: #d4dada;
				}
				.pagination .next a, .pagination .prev a {
					text-transform: uppercase;
					font-size: 12px;
				}
				.pagination .currentpage a {
					background-color: #518acb;
					color: #fff;
				}
				.pagination .currentpage a:hover {
					background-color: #518acb;
				}
			</style>
			<script>
				window.onload = function() {
					var chart = new CanvasJS.Chart("chartContainer", {
						animationEnabled: true,
						title: {
							text: "График с распределением всех проектов по бюджету"
						},
						subtitles: [{
							text: "Pie chart"
						}],
						data: [{
							type: "pie",
							yValueFormatString: "#,##0.00\"%\"",
							indexLabel: "{label} ({y})",
							dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
						}]
					});
					chart.render();
				}
			</script>
		</head>
	<body>
		<div class="container">
			<h2>Freelancehunt Projects</h2>
			<p>Таблица открытых проектов в категориях Веб-программирование, PHP и Базы данных</p>
			<div class="table">
			<table>
				<tr>
					<th>Project</th>
					<th>Price (UAH)</th>
					<th>User Name</th>
					<th>User Login</th>
				</tr>
				<?php while ($row = $result->fetch_assoc()): ?>
				<tr>
					<td><a href="<?php echo $row['project_link']; ?>" target="_blank"><?php echo $row['project_name']; ?></a></td>
					<td><?php echo $row['project_price']; ?></td>
					<td><?php echo $row['user_name']; ?></td>
					<td><?php echo $row['user_login']; ?></td>
				</tr>
				<?php endwhile; ?>
			</table>
			<?php if (ceil($total_pages / $num_results_on_page) > 0): ?>
			<ul class="pagination">
				<?php if ($page > 1): ?>
				<li class="prev"><a href="?page=<?php echo $page-1 ?>">Prev</a></li>
				<?php endif; ?>

				<?php if ($page > 3): ?>
				<li class="start"><a href="?page=1">1</a></li>
				<li class="dots">...</li>
				<?php endif; ?>

				<?php if ($page-2 > 0): ?><li class="page"><a href="?page=<?php echo $page-2 ?>"><?php echo $page-2 ?></a></li><?php endif; ?>
				<?php if ($page-1 > 0): ?><li class="page"><a href="?page=<?php echo $page-1 ?>"><?php echo $page-1 ?></a></li><?php endif; ?>

				<li class="currentpage"><a href="?page=<?php echo $page ?>"><?php echo $page ?></a></li>

				<?php if ($page+1 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page"><a href="?page=<?php echo $page+1 ?>"><?php echo $page+1 ?></a></li><?php endif; ?>
				<?php if ($page+2 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page"><a href="?page=<?php echo $page+2 ?>"><?php echo $page+2 ?></a></li><?php endif; ?>

				<?php if ($page < ceil($total_pages / $num_results_on_page)-2): ?>
				<li class="dots">...</li>
				<li class="end"><a href="?page=<?php echo ceil($total_pages / $num_results_on_page) ?>"><?php echo ceil($total_pages / $num_results_on_page) ?></a></li>
				<?php endif; ?>

				<?php if ($page < ceil($total_pages / $num_results_on_page)): ?>
				<li class="next"><a href="?page=<?php echo $page+1 ?>">Next</a></li>
				<?php endif; ?>
			</ul>
			</div>
			<?php endif; ?>
			
			<?php 
			
			$sql = "SELECT user_skills FROM db_project";
			$result_id = $conn->query($sql);		
			while($row = $result_id->fetch_array()) {
				$user_skills .= $row[user_skills] . ',';
				
			}
			$user_skills_arr = explode(',', $user_skills);
			array_pop( $user_skills_arr );
			$grupeg_skills_arr = array_count_values($user_skills_arr);
			arsort($grupeg_skills_arr);
			
			?>
			<p>Таблица со статистикой всех открытых проектов по навыкам</p>
			<div class="table">
				<table>
					<tr>
						<th>Навык</th>
						<th>Количество открытых проектов</th>
					</tr>		
				<?php
				foreach ($grupeg_skills_arr as $key => $value) {
					?>
					<tr>
						<td><?php echo $all_skils[$key]; ?></td>
						<td><?php echo $value; ?></td>
					</tr>
					<?php
				}
				?>
				</table>
			</div>
			
			<?php 
			
			?>
			</br>
			<div id="chartContainer" style="height: 370px; width: 100%;"></div>

		</div>
		<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	</body>
	</html>
<?php 
}
$conn->close(); // Close db

