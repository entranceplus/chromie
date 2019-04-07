<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add New Links</title>
    <link rel="stylesheet" href="https://bulma.io/css/bulma-docs.min.css?v=201904032112">
</head>
<body>
    <div id="deleteResult"></div>
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

    <script>
        function showLinks(){
            if( document.getElementById('saveLinkRes') != null){
                document.getElementById('saveLinkRes').innerHTML = "";
            }
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
        // document.getElementById('addLinkForm').onsubmit= function(event){
        //     event.preventDefault();
        //     document.getElementById('linkErrorMsg').innerHTML = "Verifying Link...";
        //     var link = document.getElementById('urlText').value;
        //     var xhttp = new XMLHttpRequest();
        //     xhttp.onreadystatechange = function(){
        //         if(this.readyState == 4 && this.status == 200){
        //             submitLink(link);
        //         }
        //         else if(this.status == 404){
        //             document.getElementById('linkErrorMsg').innerHTML = "link not valid";
        //         }
        //     }
        //     //to deal with No Access-Control-Allow-Origin header problem the heroku link is prepended.
        //     xhttp.open('GET','https://cors-anywhere.herokuapp.com/'+link);
        //     xhttp.send();
        // }

        // function submitLink(link){

        // }

    </script>
</body>
</html>