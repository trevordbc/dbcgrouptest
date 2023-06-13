//Bind an on-click play handler to the example stream links on the page.
    var exampleLinks = document.getElementsByClassName('streamLink');
	var callLetters = sgplayer_data.call_letters_lower;
	var frequency_lower = sgplayer_data.frequency_lower;
    //Walk all example links
    for(var i = 0, j = exampleLinks.length; i < j; i++) {
        //Bind the play stream on click handler to the stream link element.
        exampleLinks[i].onclick = function(event) {
            event.preventDefault();
            //Get the stream name from the clicked anchor element so we can
            //attempt to load its corresponding stream configuration object
            //into the player.
            var streamName = this.getAttribute('data-stream');
            //If the streamName exists in our page global streams object,
            //load the stream into the player.
            if(STREAMS[streamName] && PLAYER) {
                playStream(PLAYER, STREAMS[streamName]);
            }
        };
    }
    //The SGplayer instance will be stored here when it is fully loaded.
        var PLAYER = null;
        //Stream configuration objects can be added here for easier organization.
        var STREAMS = {
            wkrr:{
                name: callLetters + ' ' + frequency_lower,
                title: callLetters + ' ' + frequency_lower,
                fallbackArt:"https://player.streamguys.com/dbc/" + callLetters + "-" + frequency_lower + "/sgplayer/include/image/fallback.png",
                fallbackArtBG:"https://player.streamguys.com/dbc/" + callLetters + "-" + frequency_lower + "/sgplayer/include/image/fallback_bg.png",
                type:"audio",
                source:[{
                    type:"audio/aac",
                    src:"https://dbc.streamguys1.com/" + callLetters + "-" + frequency_lower + ".aac"
                }],
                metaData:{
                    enabled:true,
                    autoScrollSong:false,
                    autoScrollPrev:false,
                    songArraySize:15,
                    sgmd:{
                        source:"//jetio.streamguys.com",
                        key:"441ed8d810ca55964e9e98a9407fdfa5fe02cf2b",
                        scraperId:"6b3b4b9b-c7af-4d94-bf24-05a756ecab0f",
                        socketIO:{ forceNew:true }
                    },
                    delay:false,
                    delimiter:" - ",
                    albumOrder:3,
                    artistOrder:1,
                    trackOrder:2
                }
            }
        };
        function playStream(player, config, callback) {
            //SGplayer createStreams takes an array, so lets wrap our single stream config in an array literal.
            //Note: This call overwrites the SGplayer's existing streams array with
            //a new single index array for the given stream configuration.
            player.streams = player.createStreams([config]);
            player.config.streams = player.streams;

            player.switchStream(player.streams[0], function(success) {

                player.play();

                if(typeof callback === 'function') {
                    return callback(success);
                }
            });
        }
        function startPlayer(player, config, callback) {
            player.streams = player.createStreams([config]);
            player.config.streams = player.streams;
            player.activeStream = player.streams[0];

            player.start(function(success) {
                if(typeof callback === 'function') {
                    return callback(success);
                }
            });
        }
        window.onSGPlayerReady = function(player) {
            //player is the initalized SGplayer instance.
            //The page SGplayer instance can also be accessed with the
            //window._sgplayer reference.
            //console.log("SGplayer is now ready: ", player);

            //Start the SGplayer instance now that it is ready.
            startPlayer(player, STREAMS.wkrr, function(success) {

                //When this callback fires, SGplayer is fully loaded and started.
                //console.log("SGplayer start success: ", success);

                //Store the player reference for later usage.
                PLAYER = player;
            });
        };
        if (window.self === window.top) {
        // If not inside an iframe, create the script tag
        var sgplayerEmbed = document.createElement('script');
        sgplayerEmbed.type = 'text/javascript';
        sgplayerEmbed.id = 'sgplayerembed';
        sgplayerEmbed.src = 'https://player.streamguys.com/dbc/' + callLetters + '-' + frequency_lower + '/sgplayer/embed.min.js?v=3.3.2';

        // Append the script tag to the head
        document.head.appendChild(sgplayerEmbed);
    }