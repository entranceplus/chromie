<?php
public function makeRequest(){
        $client = new Client([
            // 'base_uri' => 'https://www.webnots.com/what-is-http/'
            'base_uri' => 'https://www.blizzard.com/en-us/'
        ]);
        $response = $client->request('GET');
        $tempResp = $response->getBody()->read(99999999);
        $tempResp = preg_replace('/\s+/', ' ', $tempResp);
        Storage::disk('local')->put('file1_new.html', $tempResp);
        $handle_old = @fopen(__DIR__.'/../../../storage/app/file1.html', 'r');
        $handle_new = @fopen(__DIR__.'/../../../storage/app/file1_new.html', 'r');
        if($handle_old && $handle_new){
            while(($buffer_old = fgets($handle_old)) !== false && ($buffer_new = fgets($handle_new)) !== false){
                if(!strcasecmp($buffer_new,$buffer_old)){
                    dd('not similar');
                }
            }
        }
        else{
            dd("error in opening files");
        }
            while(($buffer = fgets($handle)) !== false){
                $x.= $buffer;
                $count++;
            }
            fclose($handle);
        
        dd($count);
        dd(gettype($response->getBody()->read(9999999)));
        $stream = Psr7\stream_for($response->getBody()->read(99999999));
        dd($stream->read(33));
        dd($response->getBody()->read(99999999));
        dd($response);
        dd('a');
        $html = file_get_html('https://www.blizzard.com/en-us/');
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

public function compareDOMTrees($id){
    $html_old = file_get_html(__DIR__.'/../../../storage/app/file'.$id.'.html');
    $html_new = file_get_html(__DIR__.'/../../../storage/app/file'.$id.'_new.html');
    if(!empty($html_old) && !empty($html_new)){
        $root_old = $html_old->find('html')[0];
        $root_new = $html_new->find('html')[0];
        $qObj_old = array();
        $qChildAdded_old = array(); 
        $qObj_new = array();
        $qChildAdded_new = array(); 
        $rear_old = -1;
        $rear_new = -1;
        $allContent = "";
        $tempRev_old = array_reverse($root_old->children);
        $tempRev_new = array_reverse($root_new->children);
        foreach($tempRev_old as $x){
            ++$rear_old;
            $qObj_old[$rear_old] = $x; 
            $qChildAdded_old[$rear_old] = -1;
        }
        foreach($tempRev_new as $x){
            ++$rear_new;
            $qObj_new[$rear_new] = $x; 
            $qChildAdded_new[$rear_new] = -1;
        }
    }
    
    
    // dd($root->children);
    
    
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