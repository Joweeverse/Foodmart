<?php
namespace App\Controllers;
class NewsController extends BaseController{
public function index(){
if (session()->get('num_user') == '') {
return redirect()->to('/login');}
$url = 'https://www.antaranews.com/rss/terkini.xml';
$doc = new \DOMDocument(); $doc->load($url);
$newsData = $doc->getElementsByTagName("item");
foreach($newsData as $newsDataParse){
$titles = $newsDataParse->getElementsByTagName( "title" ); $title = $titles->item(0)->nodeValue;
$links = $newsDataParse->getElementsByTagName( "link" ); $link = $links->item(0)->nodeValue;
$pubDates = $newsDataParse->getElementsByTagName( "pubDate" ); $pubDate = $pubDates->item(0)->nodeValue;
$descriptions = $newsDataParse->getElementsByTagName( "description" ); $description =
$descriptions->item(0)->nodeValue;
$data['title'][] = $title; $data['link'][] = $link; $data['pubDate'][] = $pubDate;
$data['description'][] = $description;
continue;}
return view('header', $data).view('menu').view('news').view('footer');}
}