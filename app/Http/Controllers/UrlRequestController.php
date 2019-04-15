<?php

namespace App\Http\Controllers;

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
            if(Link::select('*')->where(['url'=>$requestM->input('link'), 'user_id' => Auth::user()->id])->count() == 0){
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
        $linksArr = Link::select('id','url')->get();
        foreach($linksArr as $link){
            $client = new Client([
                'base_uri' => $link->url
            ]);
            $response = $client->request('GET');
            if($response->getStatusCode() == 200){
                $sourceCode = $response->getBody()->read(99999999);
                $sourceCode = preg_replace('/\s+/', ' ', $sourceCode);
                $retVal = $this->generateDiff($link->id, $sourceCode);
                if($retVal == 0){
                    continue;
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
        $this->notifyUsers();
    }

    public function generateDiff($id, &$sourceCode){
        $x = Link::find($id)->link_content;
        if(!empty($x) && !empty($sourceCode)){
            $htmlDiff = new HtmlDiff($x, $sourceCode);
            $hasDiffCheckStr1 = 'class="diffmod"';
            $hasDiffCheckStr2 = '<ins>';
            $hasDiffCheckStr3 = '<del>';
            $content = $htmlDiff->build();
            $colorizer = "@extends('layouts.app')
            @section('assets')<style>
            ins{background-color: lightgreen;}
            del{background-color: lightcoral;}
            </style>@endsection  @section('content')";
            if(strpos($content, $hasDiffCheckStr3) !== false|| strpos($content, $hasDiffCheckStr2) !== false|| strpos($content, $hasDiffCheckStr1) !== false){
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
        $x = Auth::user()->links()->select('id','url','link_diff')->orderby('updated_at', 'desc')->get();
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
        if(Link::find($id)->user->id == Auth::user()->id){
            if(Storage::disk('view')->put('genFile.blade.php', Link::find($id)->link_diff)){
                return view('genFile');
            }
        }
        return view('addLinks',['diffFailMsg' => 'Error in fetching changes.']);
    }

    public function notifyUsers(){
        $ulmap = array();
        $users = [];
        foreach($this->linkChangedId as $y){
            $cc = Link::find($y)->user;
            $users[] = $cc;
            if(!isset($ulmap[$cc->id])){    
                $ulmap[$cc->id] = array();
            }
            array_push($ulmap[$cc->id], $y);
        }
        $changedUrls = array();
        $users = array_values(array_unique($users));
        foreach($users as $x){
            foreach($ulmap[$x->id] as $y){
                $changedUrls[] = Link::find($y)->url;
            }
            Mail::to($x->email)->send(new LinksChanged($changedUrls));
            $changedUrls = array();
        }
    }
};

?>
