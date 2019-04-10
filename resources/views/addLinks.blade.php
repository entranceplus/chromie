@extends('layouts.app')
@section('assets')
    <link rel="stylesheet" href="https://bulma.io/css/bulma-docs.min.css?v=201904032112">
@endsection
@section('content')
    <div id="deleteResult"></div>
    @if(isset($diffFailMsg))
        <b>{{$diffFailMsg}}</b>
    @endif
    @if(isset($success))
        @if($success == 0)
            <p id="saveLinkRes">Error in adding to DB</p>
        @elseif($success == 1)
            <p id="saveLinkRes">Link was added to DB</p>
        @elseif($success == 2)
            <p id="saveLinkRes">error in accessing link</p>
        @elseif($success == 3)
            <p id="saveLinkRes">Link already exists</p>
        @endif
    @endif
    <section class="section ">
        <div class="container">
            <form id="addLinkForm" action="/saveLink" method="POST">
                @csrf
                <div class="field is-grouped column">
                    <div class="control is-expanded">
                        <input class="input" type="url" id='urlText' name='link' required placeholder="Enter a Link">
                        <div id="linkErrorMsg"></div>
                    </div>
                    <div class="control">
                        <button class="button is-primary">Add Link</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <button class="button is-primary" onclick="showLinks()">Show All Links</button>
        </div>
        <div class="container" id="linksDiv">

        </div>
    </section>

    <section class="section">
        <div class="container">
            <button class="button is-primary" onclick="getChangedLinks()">Show Changed Links</button>
        </div>
        <div class="container" id="changedLinksId"></div>
    </section>

    <script>
        // prevent form from resubmitting
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
        function showLinks(){
            if( document.getElementById('saveLinkRes') != null){
                document.getElementById('saveLinkRes').innerHTML = "";
            }
            document.getElementById('deleteResult').innerHTML = "";
            var table = '<table class="table">\
                <thead><tr><th>S/n</th><th>Url</th><th>Added on</th><th>Delete</th>\
                </thead><tbody>';
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.status == 200 && this.readyState == 4){
                    var x = JSON.parse(this.response);
                    for(var i = 0; i < x.length; i++){
                        table += '<tr>\
                        <td>'+(i+1)+'</td>\
                        <td>'+x[i].url+'</td>\
                        <td>'+x[i].created_at+'</td>\
                        <td><button class= "button is-danger" onclick="deleteLink('+x[i].id+')">Delete</button></td>\
                        </tr>';
                    }
                    table += '</tbody></table>';
                    document.getElementById('linksDiv').innerHTML = table;
                }
            }
            xhttp.open('GET','/showLinks');
            xhttp.send();
        }
        function deleteLink(id){
            if( document.getElementById('saveLinkRes') != null){
                document.getElementById('saveLinkRes').innerHTML = "";
            }
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.status == 200 && this.readyState == 4){
                    var res = JSON.parse(this.responseText);
                    console.log(res);
                    if(res == 'true'){
                        document.getElementById('deleteResult').innerHTML = "Link was removed.";
                    }
                    else{
                        document.getElementById('deleteResult').innerHTML = "error in removing Link";
                    }
                    showLinks();
                }
            }
            xhttp.open('GET','/deleteLink/'+id);
            xhttp.send();
        }
        function getChangedLinks(){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.status == 200 && this.readyState == 4){
                    var table = '<table class="table">\
                    <thead><tr><th>S/n</th><th>Url</th>\
                    </thead><tbody>';
                    var x = JSON.parse(this.response);
                    for(var i = 0; i < x.length; i++){
                        table += '<tr>\
                            <td>'+(i+1)+'</td>\
                            <td><a href="showDiff/'+x[i].id+'">'+x[i].url+'</a></td>\
                            </tr>';
                    }
                    table += '</tbody></table>';
                    document.getElementById('changedLinksId').innerHTML = table;
                }
            }
            xhttp.open('GET','/changedLinks');
            xhttp.send();
        }

    </script>
@endsection