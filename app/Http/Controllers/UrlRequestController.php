<?php

namespace App\Http\Controllers;
// require('simple_html_dom.php');

use App\Link;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Mail\LinksChanged;
use Caxy\HtmlDiff\HtmlDiff;
use Illuminate\Http\Request;
use GuzzleHttp\Stream\Stream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UrlRequestController extends Controller
{
    public $linkChangedId;

    public function saveLink(Request $requestM){
        $client = new Client([
            'base_uri' => $requestM->input('link')
        ]);
        $response = $client->request('GET');
        if($response->getStatusCode() == 200){
            if(Link::select('*')->where(['url'=>$requestM->input('link')])->count() == 0){
                if(Auth::user()){
                    $sourceCode = $response->getBody()->read(99999999);
                    $sourceCode = preg_replace('/\s+/', ' ', $sourceCode);
                    $row = Link::create(['user_id'=>Auth::user()->id, 'url' => $requestM->input('link'), 'link_content' => $sourceCode]);
                    return view('addLinks',['success' => 1]);
                }
                else{
                    return view('addLinks',['success' => 0]);
                }
            }
            else{
                return view('addLinks',['success' => 3]);
            }
        }
        else{
            return view('addLinks',['success' => 2]);
        }
    }

    public function showLinks(){
        $temp = Auth::user()->links()->get();
        return response()->json($temp);
    }

    public function deleteLink($id){
        if(Auth::user()->id){
            if(Link::find($id)->delete()){
                return response()->json('true');
            }
            else{
                return reseponse()->json('false');
            }
        }    
    }  

    public function findDiff(){
        $this->linkChangedId = array();
        $linksArr = Link::all();
        foreach($linksArr as $link){
            $client = new Client([
                'base_uri' => $link->url
            ]);
            $response = $client->request('GET');
            if($response->getStatusCode() == 200){
                $sourceCode = $response->getBody()->read(99999999);
                $sourceCode = preg_replace('/\s+/', ' ', $sourceCode);
                if(Storage::disk('local')->put('file'.$link->id.'_new.html', $sourceCode)){
                    Log::debug('callin diff for url:'.$link->url);
                    $retVal = $this->generateDiff($link->id, $sourceCode);
                    if($retVal == 0){
                        continue;
                    }
                }
                else{
                    //handle not able to create new file 
                    continue;
                }
            }
            else{
                //handle link not accessible errors
                continue;
            }
        }
        $changedUrls = array();
        Log::info(print_r($this->linkChangedId, true));
        if(!empty($this->linkChangedId)){
            foreach($this->linkChangedId as $id){
                $changedUrls[] = $linksArr->find($id)->url;
            }
            Mail::to('rvkmr0851@gmail.com')->send(new LinksChanged($changedUrls));
        }
    }

    public function generateDiff($id, &$sourceCode){
        $x = Link::find($id)->link_content;;
        if(!empty($x) && !empty($sourceCode)){
            $htmlDiff = new HtmlDiff($x, $sourceCode);
            $hasDiffCheckStr1 = 'class="diffmod"';
            $hasDiffCheckStr2 = '<ins>';
            $hasDiffCheckStr3 = '<del>';
            $content = $htmlDiff->build();
            $colorizer = "@extends('layouts.app')
            @section('assets')<style>
            ins{background-color: Green;}
            del{background-color: Red;}
            </style>@endsection  @section('content')";
            if(strpos($content, $hasDiffCheckStr3)|| strpos($content, $hasDiffCheckStr2)|| strpos($content, $hasDiffCheckStr1)){
                $content = $colorizer.$content.'@endsection';
                DB::table('links')->where('id','=',$id)->update(['link_diff' => $content, 'link_content' => $sourceCode]);
                $this->linkChangedId[] = $id;
            }
            return 1;
        }
        else{
            //handle not able to fetch files
            return 0;
        }        
    }

    public function changedLinks(){
        $x = Auth::user()->links()->select('id','url','link_diff')->get();
        $changedArr = array();
        foreach($x as $p){
            if($p->link_diff != null){
                unset($p->link_diff);
                $changedArr[] = $p;
            }
        }
        return response()->json($changedArr);
    }

    public function showDiff($id){
        if($x = Auth::user()->links()->get()->find($id)){
            Storage::disk('view')->put('genFile.blade.php', $x->link_diff);
            return view('genFile');
        }
        return view('addLinks',['diffFailMsg' => 'Error in fetching changes.']);
    }

    public function abc(){
        // Storate::disk('local')->put('f1.blade.php','');
        // return view('genFile');
    }
};

?>
