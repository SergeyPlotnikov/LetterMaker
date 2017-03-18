<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 18.03.2017
 * Time: 14:56
 */
require_once 'vendor/autoload.php';
$urls = [];
for ($i = 1; $i <= count($_POST); $i++) {
    $urls[] = strip_tags($_POST["url$i"]);
}


//Парсинг страницы
function parse($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    $doc = phpQuery::newDocument($html);
    //Парсим url картинки
    $imageStyle = $doc->find('div.swiper-wrapper>div')->attr('style');
    $matches = [];
    $pattern = '/\(\'(.+)\'\)/';
    preg_match($pattern, $imageStyle, $matches);
    $imageUrl = $matches[1];
    $performance['url'] = $imageUrl;
    //Парсим описание спектакля
    $description = $doc->find('div.event-info>div.col>p')->text();
    $performance['description'] = $description;
    return $performance;
}


//Функция замены url и описания в шаблоне
function change($performances)
{
    $file = file_get_contents('template.html');
    $doc = phpQuery::newDocument($file);
    $imgs = $doc->find('tr>td>a>img:odd:not(:first):not(:last))');
    $i = 0;
    foreach ($imgs as $img) {
        $img = pq($img);
        $img->attr('src', $performances[$i]['url']);
        $i++;
    }

    $description = $doc->find('table+table>tbody>tr+tr>td>span');
    $i = 0;
    foreach ($description as $desc) {
        pq($desc)->text($performances[$i]['description']);
        $i++;
    }
    file_put_contents('template.html', $doc);
}


$performances = [];
for ($i = 0; $i < count($urls); $i++) {
    $performance = parse($urls[$i]);
    $performances[] = $performance;
}

change($performances);
echo file_get_contents('template.html');
