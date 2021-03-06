<script src="jquery.js"></script>
<script src="bootstrap.js"></script>
<script src="https://apis.google.com/js/client.js?onload=googleApiClientReady"></script>
<script src="https://apis.google.com/js/api.js"></script>

<link rel="stylesheet" href="font-awesome/css/font-awesome.css">
<link rel="stylesheet" href="bootstrap.css">

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v6.0&appId=297547070901697"></script>

<style>
	body {
		font-family: Tahoma;
	}
	#videos {
		margin-top: 50px;
		margin-bottom: 50px;
		display: grid;
    	grid-template-columns: repeat(3, 1fr);
	}
	.fa-thumbs-up,
	.fa-thumbs-down {
		margin-left: 20px;
	}
	.panel-heading {
		margin-top: 50px;
		margin-bottom: 20px;
	}
	.media-body {
		margin-left: 10px;
	}
	#btns{display:grid;}
	button, select, input {
    	width: fit-content;
		margin: 10px;
	}
</style>

<div class="container">
	<h1 class="text-center" style="margin-top: 50px;">Youtube Videos API</h1>
	<p class="text-center">Read & Write Data from YouTube API</p>

    <div id="login-container" class="pre-auth d-none">This application requires access to your YouTube account.
      Please <a href="#" id="login-link">authorize</a> to continue.
    </div>

	<h2>Videos</h2>

	<ul id="videos" class="list-group"></ul>

	<div id="video-detail"></div>

	<div class="row bootstrap snippets">
	    <div class="col-md-6 col-md-offset-2 col-sm-12">
	        <div class="comment-wrapper">
	            <div class="panel panel-info">
	                <div class="panel-heading">
	                    <h2>Comments</h2>
	                </div>

       				<span id="status"></span>

	                <div class="panel-body">
	                	<div class="clearfix"></div>
	                    <hr>
	                    <ul id="comments" class="media-list">

	                    </ul>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>

</div>

<script>
	var key = "";
	var channelId = "UCh3QGrDc0Y4eUef3Rt2r7ZA";
	var url = "https://www.googleapis.com/youtube/v3/search?key=" + key + "&channelId=" + channelId + "&part=snippet,id&maxResults=9";

	var access_token= ''; 
	// After the API loads, call a function to enable the video rating
	function handleAPILoaded(authResult) {
		access_token = authResult.access_token;
	enableForm();
	}

	function enableForm() {
	}

	setTimeout(function () {
		$.ajax({
			url: url,
			method: "GET",
			success: function (response) {

				var html = "";

				for (var a = 0; a < response.items.length; a++) {
					if (response.items[a].id.kind == "youtube#video") {
						html += "<li class='list-group-item'>";
							html += "<a id='link' href='javascript:void(0);' onclick='videoDetail(this);' data-id='" + response.items[a].id.videoId + "' comment-id='" + response.items[a].id.videoId + "'>";
								html += response.items[a].snippet.title;
							html += "</a>";
						html += "</li>";
					}
				}

				$("#videos").html(html);
			}
		});
	}, 100);

	function videoDetail(self) {
		var videoId = self.getAttribute('data-id');
		var url = "https://www.googleapis.com/youtube/v3/videos?key=" + key + "&id=" + videoId + "&part=statistics,snippet";
		$.ajax({
			url: url,
			method: "GET",
			success: function (response) {
				//console.log(response);

				var html = "";
				html += "<div class='row'><div class='col-md-12'><h1>" + response.items[0].snippet.title + "</h1></div></div>";
				html += "<div class='row'><div class='col-md-12'><h3 class='text-left'><i class='fa fa-eye'></i> " + response.items[0].statistics.viewCount + " <i class='fa fa-thumbs-up'></i> " + response.items[0].statistics.likeCount + " <i class='fa fa-thumbs-down'></i> " + response.items[0].statistics.dislikeCount + "</h3></div></div>";
				html += '<iframe id="player" type="text/html" width="640" height="390" src="https://www.youtube.com/embed/'+videoId+'?enablejsapi=1" frameborder="0"></iframe>';
				html += "<div class='row'><div class='col-md-12'><pre>" + response.items[0].snippet.description + "</pre></div></div>";

				$("#video-detail").html(html);

				getComments(videoId);
			}
		});
	}

	function getComments(videoId) {
		var url = "https://www.googleapis.com/youtube/v3/commentThreads?key=" + key + "&part=snippet&videoId=" + videoId + "&maxResults=5&order=time";

		$.ajax({
			url: url,
			method: "GET",
			success: function (response) {
				var html = '<div id="btns">';	
				
				html += '<div style="display:block;"><input type="text" value="" name="comment" id="comment" placeholder="Insert your comment here..."><input type="button" id="btnComment" comment-id="'+videoId+'" Value="Post Comment to YouTube" onclick="authenticate().then(loadClient).then(execute)"></div>';
                html += '<button id="btnLike" like-id="'+videoId+'" onclick="authenticate().then(loadClient).then(executeRate)">Like Video on YouTube</button>'
				html += '<div class="fb-share-button" data-href="https://www.youtube.com/watch?v="'+videoId+'" data-layout="button" data-size="small"><a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D'+videoId+'&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore"><button>Share on Facebook</button></a></div>';

				html += '</div>';

				for (var a = 0; a < response.items.length; a++) {
					var commenterName = response.items[a].snippet.topLevelComment.snippet.authorDisplayName;
					var authorProfileImageUrl = response.items[a].snippet.topLevelComment.snippet.authorProfileImageUrl;
					var textDisplay = response.items[a].snippet.topLevelComment.snippet.textDisplay;
					var publishedAt = response.items[a].snippet.topLevelComment.snippet.publishedAt;

					var date = publishedAt.split("T")[0];
					date += " " + publishedAt.split("T")[1].split(".")[0];
					
					html += '<li class="media">';
                        html += '<a href="#" class="pull-left">';
                            html += '<img src="' + authorProfileImageUrl + '" alt="" class="img-circle">';
                        html += '</a>';
                        
                        html += '<div class="media-body">';
                            html += '<span class="text-muted pull-right">';
                                html += '<small class="text-muted">' + date + '</small>';
                            html += '</span>';
                            
                            html += '<strong class="text-success">@' + commenterName + '</strong>';
                            html += '<p>' + textDisplay + '</p>';
                        html += '</div>';
                    html += '</li>';
				}

				$("#comments").html(html);				
			}
		});
	}

  /**
   * Sample JavaScript code for youtube.commentThreads.insert
   * See instructions for running APIs Explorer code samples locally:
   * https://developers.google.com/explorer-help/guides/code_samples#javascript
   */

  function authenticate() {
    return gapi.auth2.getAuthInstance()
        .signIn({scope: "https://www.googleapis.com/auth/youtube.force-ssl"})
        .then(function() { console.log("Sign-in successful"); },
              function(err) { console.error("Error signing in", err); });
  }
  function loadClient() {
    gapi.client.setApiKey("");
    return gapi.client.load("https://www.googleapis.com/discovery/v1/apis/youtube/v3/rest")
        .then(function() { console.log("GAPI client loaded for API"); },
              function(err) { console.error("Error loading GAPI client for API", err); });
  }
  // Make sure the client is loaded and sign-in is complete before calling this method.
  function execute(self) {
	var videoId = $("#btnComment").attr("comment-id");
	//console.log("videoId: "+videoId);
	var comment = $('#comment').val();
    return gapi.client.youtube.commentThreads.insert({
      "part": "snippet",
      "resource": {
        "snippet": {
          "videoId": videoId,
          "topLevelComment": {
            "snippet": {
              "textOriginal": comment
            }
          }
        }
      }
    }).then(function(response) {
    	// Handle the results here (response.result has the parsed body).
            console.log("Response", response);
        },
        function(err) { console.error("Execute error", err); });
  }

  function executeRate(self) {
	var videoId = $("#btnLike").attr("like-id");
	//console.log("videoId from likebtn: "+videoId);
    return gapi.client.youtube.videos.rate({
      "id": videoId,
      "rating": "like"
    })
    .then(function(response) {
        // Handle the results here (response.result has the parsed body).
        console.log("Response", response);
    },
    function(err) { console.error("Execute error", err); });
  }


  gapi.load("client:auth2", function() {
    gapi.auth2.init({client_id: "858116069923-lk7vcdnejiahs7hnic82dp2a4f8c0jqb.apps.googleusercontent.com"});
  });

</script>

