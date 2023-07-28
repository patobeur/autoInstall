<?php
	define('ROOTS',array('debug'=>true)); 
	define('CONF', array('F'=>'F')); 
trait Pat
{	
	// Some'Tools
	// ---------------------------------------------------------------
	static function get_clean($data)
	{
        // Nettoyer les données en supprimant les caractères spéciaux et en échappant les apostrophes
		if (is_array($data) && count($data) > 0) {
			for ($i = 0; $i < count($data); $i++) {
				$item = trim($data[$i]);
				$item = stripslashes($item);
				$item = htmlspecialchars($item, ENT_QUOTES);
				$data[$i] = $item;
			}
		} else {
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data, ENT_QUOTES);
		}
		return $data;
	}
	// ---------------------------------------------------------------
	// DATES TEXTS & OTHER VALUES TOOLS
	// ---------------------------------------------------------------
	static function get_diffDate($debut, $fin)
	{
		$dif = ceil(abs($fin - $debut) / 86400);
		return $dif;
	}
	// ---------------------------------------------------------------
	static function dateDuJour($format = 'classic')
	{
		switch ($format) {
			case 'arrivalDate':
				$date = date('d/m/Y', time());
				break;
			case 'arrivalHour':
				$date = date('H:i:s', time());
				break;
			case 'timestamp':
				$date = date('d/m/Y H:i:s', time());
				break;
			case 'hourfull':
				$date = date('H:i:s');
				break;
			case 'dayfull':
				$date = date('Y-m-d');
				break;
			case 'classic':
				$date = date('Y-m-d H:i:s');
				break;
			case 'filename':
				$date = date('Ymd_Hi');
				break;
		}
		return $date;
	}
	/**
	 * TagMeThis encapsulater html
	 *
	 * @param string $tag html tag (default=span)
	 * @param string $htmlcontent html content
	 * @param string $class css class name
	 * @return void
	 */
	static function TagMeThis($tag = 'span', $htmlcontent = false, $class = false, $chaos = false)
	{
		if (($htmlcontent and $tag)) {
			$class = " " . $class;
			switch ($tag) {
				case 'span';
				case 'div';
				case 'td';
				case 'tr';
				case 'li';
				case 'ul';
				case 'ol';
				case 'i';
				case 'pre';
					$htmlcontent = '<' . $tag . ' class="fun' . $class . '">' . $htmlcontent . '</' . $tag . '>';
					break;
				case 'span';
					$htmlcontent = '<' . $tag . ' class="fun' . $class . '">' . $htmlcontent . '</' . $tag . '>';
					break;
				case 'br';
					$htmlcontent = $htmlcontent . '<br/>';
					break;
				case 'href';
					if ($chaos && $chaos['href']) {
						$htmlcontent = '<a href="' . $chaos['href']['href'] . '" class="fun' . $class . '" target="' . $chaos['href']['target'] . '" title="' . $chaos['href']['title'] . '">' . $htmlcontent . '</a>';
					}
					break;
				case 'submit';
					$htmlcontent = '<button type="submit" class="fun' . $class . '">' . $htmlcontent . '</button>';
					break;
				case 'form';
					if ($chaos) {
						$tempo = '<form';
						$tempo .= (isset($chaos['form']['action']) ? ' action="' . $chaos['form']['action'] . '"' : '');
						$tempo .= ' method="post"';
						$tempo .= (isset($chaos['form']['name']) ? ' name="' . $chaos['form']['name'] . '"' : '');
						$tempo .= '>' . $htmlcontent . '</form>';
						$htmlcontent = $tempo;
					}
					break;
				case 'textarea';
					$tempo = '<textarea class="fun' . $class . '"';
					$tempo .= ' rows="5" cols="33"';
					$tempo .= '>' . $htmlcontent . '</textarea>';
					$htmlcontent = $tempo;
					break;
			}
		}
		return $htmlcontent ?? '';
	}
	/**
	 * clean print_r function
	 * @param string $paquet array give me something to print like a string
	 * @param string $title string give me title
	 * @param boolean $top true make display without br before content
	 * @param boolean $hr true display line before content
	 * @return void
	 */
	static function print_air($paquet, $title = '', $top = false, $hr = false)
	{
		if (ROOTS['debug']) {
			$hr = (!empty($hr)) ? "<hr>" : "";
			$br = (!empty($top)) ? '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>' : "";
			// if (!headers_sent()) {
			// 	header(WEBSITE['header']);
			// }
			print($hr . $br . '<pre>');
			// echo "function(".__FUNCTION__.")<br>";
			$title ? print(CONF['F'] . $title . ': ') : ''; //print('print_r: ');
			print_r($paquet);
			print('</pre>');
		}
	}
	static function sqlToTable($datasindex, $tablenom = "Tableau de données")
	{
		if ($datasindex && is_array($datasindex)) {
			$tableau = '
			<div class="form-page">
				<div class="message">
					<div class="titre">' . $tablenom . '</div>
					<div class="table-responsive">
						<table>#LIGNES#</table>';
			$intitules = '';
			$lignes = '';
			$intituleSiLigneZero = 0;

			foreach ($datasindex as $key => $value) {
				if (is_array($value)) {
					$colonnes = '';
					foreach ($value as $key2 => $value2) {
						$colonnes .= '<td>' . (!empty($value2) ? (is_array($value2) ? 'ARRAY' : htmlentities($value2)) : 'null') . '</td>';
						if ($intituleSiLigneZero === 0) {
							$intitules .= '<td>[' . $key2 . ']</td>';
						}
					}
					$intituleSiLigneZero++;
					$lignes .= '<tr>' . $colonnes . '</tr>';
				}
			}


			$tableau = str_replace('#LIGNES#', '<thead><tr>' . $intitules . '</tr></thead><tbody>' . $lignes . '</tbody><tfoot><tr>' . $intitules . '</tr></tfoot>', $tableau);
			$tableau .= "</div></div></div>";
			return $tableau;
		}
		return false;
	}
	static function sqlToTable2($datasindex, $tablenom = "Tableau de données")
	{
		if ($datasindex && is_array($datasindex)) {
			$tableau = '
				<div class="funtable">
					<div class="titre">' . $tablenom . '</div>
					<div class="table-responsive">
						<table>#LIGNES#</table>';
			$intitules = '';
			$lignes = '';
			$intituleSiLigneZero = 0;
			$centertexte = !isset($datasindex[0]) ? '' : ' style="text-align:left;"';

			foreach ($datasindex as $key => $value) {
				if (is_array($value)) {
					$colonnes = '';
					foreach ($value as $key2 => $value2) {
						$colonnes .= '<td' . $centertexte . '>' . (!empty($value2) ? (is_array($value2) ? 'ARRAY' : nl2br(htmlentities($value2))) : 'null') . '</td>';
						if ($intituleSiLigneZero === 0) {
							$intitules .= '<td>[' . $key2 . ']</td>';
						}
					}
					$intituleSiLigneZero++;
					$lignes .= '<tr>' . $colonnes . '</tr>';
				} else {
					if (isset($value)) {
						$lignes .= "<tr><td>key:" . $value . ",value:" . $value . "</td></tr>";
					}
				}
			}


			$tableau = str_replace('#LIGNES#', '<thead><tr>' . $intitules . '</tr></thead><tbody>' . $lignes . '</tbody><tfoot><tr>' . $intitules . '</tr></tfoot>', $tableau);
			$tableau .= "</div></div>";
			return $tableau;
		}
		return false;
	}


	/**
	 * Get current user IP Address.
	 * @return string
	 */
	static function get_ip_address()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
			return $_SERVER['HTTP_X_REAL_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}
		return '';
	}
	static function generateToken()
	{
		return md5(rand(1, 10) . microtime());
	}
	static function str_aireplace($source, $remplacementIdx)
	{
		if (!empty($source) and gettype($source) === 'string' and !empty($remplacementIdx) and is_array($remplacementIdx)) {
			foreach ($remplacementIdx as $name => $content) {
				$source = str_replace($name, $content, $source);
			}
			return $source;
		}
		return false;
	}
	static function print_airZ($tab = array(), $bloc = "", $kkk = '', $strong = false, $niveau = 0)
	{
		$bloc = !empty($bloc) ? $bloc : '';
		if (is_object($tab)) {
			$tab = get_object_vars($tab);
		}
		if ($niveau != 0) {
			$bloc .= "<br/>";
		}
		foreach ($tab as $key => $value) {
			if ($strong === true) {
				$bloc .= str_repeat("&nbsp;", $niveau * 4) . "<strong>" . $key . "</strong> => ";
			} else {
				$bloc .= str_repeat("&nbsp;", $niveau * 4) . $key . " => ";
			}
			if (is_array($value) || is_object($value)) {
				$kkk = $key;
				$bloc = $this->print_airZ($value, $bloc, $kkk, $strong, $niveau + 1);
				continue;
			}
			$bloc .= ($kkk === 'errors' ? '<span style="color:red">' : '') . $value . ($kkk === 'errors' ? '</span>' : '') . "<br/>";
		}
		return $bloc;
	}
	function get_SqlToString($sql, $bind)
	{
		if (
			isset($bind) &&
			count($bind) > 0 &&
			is_array($bind)
		) {
			$sql = str_replace('?', "%s", $sql);
			$sql = vsprintf($sql, $bind);
		}
		return $sql;
	}
}
