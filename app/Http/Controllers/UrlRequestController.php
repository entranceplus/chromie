<?php

namespace App\Http\Controllers;
require('simple_html_dom.php');
use App\Link;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Stream\Stream;
use Illuminate\Support\Facades\Storage;

class UrlRequestController extends Controller
{
    public $stack_old,$top_old,$stack_new,$top_new,$allContent;
    public $x;

    public function saveLink(Request $requestM){
        $client = new Client([
            'base_uri' => $requestM->input('link')
        ]);
        $response = $client->request('GET');
        if($response->getStatusCode() == 200){
            if(Link::select('*')->where(['url'=>$requestM->input('link')])->count() == 0){
                $row = Link::create(['url' => $requestM->input('link')]);
                if($row->id){
                    $sourceCode = $response->getBody()->read(99999999);
                    $sourceCode = preg_replace('/\s+/', ' ', $sourceCode);
                    Storage::disk('local')->put('file'.$row->id.'.html', $sourceCode);
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
        $temp = Link::all();
        return response()->json($temp);
    }

    public function deleteLink($id){
        if(Link::find($id)->delete()){
            $res = Storage::disk('local')->delete(['file'.$id.'.html','file'.$id.'_new.html']);
            return response()->json('true');
        }
        else{
            return reseponse()->json('false');
        }
    }

    public function makeRequest(){
        $client = new Client([
            // 'base_uri' => 'https://www.webnots.com/what-is-http/'
            'base_uri' => 'https://www.blizzard.com/en-us/'
        ]);
        $response = $client->request('GET');
        $tempResp = $response->getBody()->read(99999999);
        $tempResp = preg_replace('/\s+/', ' ', $tempResp);
        Storage::disk('local')->put('file1_new.html', $tempResp);
        // $handle_old = @fopen(__DIR__.'/../../../storage/app/file1.html', 'r');
        // $handle_new = @fopen(__DIR__.'/../../../storage/app/file1_new.html', 'r');
        // if($handle_old && $handle_new){
        //     while(($buffer_old = fgets($handle_old)) !== false && ($buffer_new = fgets($handle_new)) !== false){
        //         if(!strcasecmp($buffer_new,$buffer_old)){
        //             dd('not similar');
        //         }
        //     }
        // }
        // else{
        //     dd("error in opening files");
        // }
        //     while(($buffer = fgets($handle)) !== false){
        //         $x.= $buffer;
        //         $count++;
        //     }
        //     fclose($handle);
        // }
        // dd($count);
        // dd(gettype($response->getBody()->read(9999999)));
        // $stream = Psr7\stream_for($response->getBody()->read(99999999));
        // dd($stream->read(33));
        // dd($response->getBody()->read(99999999));
        // dd($response);
        // dd('a');
        // $html = file_get_html('https://www.blizzard.com/en-us/');
        $html_old = file_get_html(__DIR__.'/../../../storage/app/file1.html');
        $html_new = file_get_html(__DIR__.'/../../../storage/app/file1_new.html');
        // dd($html->find('html')[0]->children[0]->children[0]->__toString());
        // dd($html->find('html')[0]->children);
        $root_old = $html_old->root->children[1];
        $root_new = $html_new->root->children[1];
        $this->stack_old = array();
        $this->stack_new = array();
        $this->top_old = -1;
        $this->top_new = -1;
        $this->allContent = "";
        $this->stack_old = $root_old->children;
        $this->stack_new = $root_new->children;
        $this->top_old += count($this->stack_old);
        $this->top_new += count($this->stack_new);
        $this->traverseTree();
        // dd($stack_old);
        Storage::disk('local')->put('file1_compx.html', $this->allContent);
    }

    public function traverseTree(){
        if($this->top_old > -1 && $this->top_new > -1){
            $temp_old = $this->stack_old[$this->top_old--];
            $temp_new = $this->stack_new[$this->top_new--];
            if(strcmp($temp_old->__toString(), $temp_new->__toString())){
                $this->allContent .= "\n"."OLD:\n\n".$temp_old->__toString()."\nNew:\n\n".$temp_new->__toString();
            }
            $tempArr_old = $temp_old->children;
            $tempArr_new = $temp_new->children;
            if(!empty($tempArr_old) && !empty($tempArr_new)){
                foreach($tempArr_old as $x){
                    $this->stack_old[++$this->top_old] = $x;
                }
                foreach($tempArr_new as $x){
                    $this->stack_new[++$this->top_new] = $x;
                }
            }
            $this->traverseTree();
        }
        else{
            return;
        }
    }

    public function traverseTreePostorder(){
        // $html = file_get_html(__DIR__.'/../../../storage/app/postorder.html');
        $html = file_get_html(__DIR__.'/../../../storage/app/file1.html');
        $root = $html->root->children[1];
        $qObj = array();
        $qChildAdded = array();
        $front = 0;
        $rear = -1;
        $allContent = "";
        // dd($root->children);
        $tempRev = array_reverse($root->children);
        foreach($tempRev as $x){
            ++$rear;
            $qObj[$rear] = $x; 
            $qChildAdded[$rear] = -1;
        }
        // dd(($qObj[$rear]->tag));
        while($rear > -1){
            if(!empty($qObj[$rear]->children) && $qChildAdded[$rear] == -1){
                $qChildAdded[$rear] = 1;
                $temp = $qObj[$rear]->children;
                $temp = array_reverse($temp);
                foreach($temp as $z){
                    ++$rear;
                    $qObj[$rear] = $z;
                    $qChildAdded[$rear] = -1;
                }
            }
            // case where its children have already been printed or it has no children
            else{
                if($qObj[$rear]->tag != "head" && $qObj[$rear]->tag != "body"){
                    $allContent .= $qObj[$rear]->__toString()."\n";
                }    
                $rear--;
            }
        }
        dd($allContent);
    }
};

?>
