<x-app-layout>

<!-- jsdelivr -->
<script src="https://cdn.jsdelivr.net/npm/artplayer/dist/artplayer.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<!-- unpkg -->
<script src="https://unpkg.com/artplayer/dist/artplayer.js"></script>
<script src="https://unpkg.com/artplayer/dist/artplayer.min.js"></script>
<script src="https://unpkg.com/artplayer-plugin-hls-quality@2.0.0/dist/artplayer-plugin-hls-quality.js"></script>
<style>
    .select-text {
    margin-left: 1120px;
}
.bg-success {
    background-color: green; /* ou une autre couleur pour "success" */
}

.bg-danger {
    background-color: red; /* ou une autre couleur pour "danger" */
}

</style>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-md-flex align-items-center mb-3 mx-2">
                        <div class="select-text">
                            <span>Select Your Channel -></span>
                        </div>
                        <button type="button"
                            class="btn btn-sm btn-white btn-icon d-flex align-items-center mb-0 ms-md-auto mb-sm-0 mb-2 me-2">
                            <span class="btn-inner--icon">
                                <span class="p-1 bg-success rounded-circle d-flex ms-auto me-2">
                                    <span class="visually-hidden">New</span>
                                </span>
                            </span>
                            <span id="nomCamera" class="btn-inner--text">Caméras</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark btn-icon d-flex align-items-center mb-0">
                            <span class="btn-inner--icon">
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="d-block me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </span>
                            <span class="btn-inner--text--resync">ReSync</span>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="my-0">
            <div class="artplayer-app" data-art-id="1" style="width: 1518px; height: 853.883px;"></div>

<!--<x-app.footer />-->
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var cameras = [
        { url: 'https://edge1-ams.live.mmcdn.com/live-hls/amlst:cherrycrush-sd-1a20833e44505b425ea591a9c6541a90b4449484c92c7fe93f69467a15db11e5_trns_h264/chunklist_w1066922240_b5128000_t64RlBTOjMwLjA=.m3u8', nom: 'cherrycrush' },
        { url: 'https://edge9-mxp.live.mmcdn.com/live-hls/amlst:madnessalise-sd-faf984648a7b8d01501cc2ac53f2355807de74b2cd5b78dac0da909b84f08ee3_trns_h264/chunklist_w765558374_b5128000_t64RlBTOjMwLjA=.m3u8', nom: 'madnessalise' },
        { url: 'https://edge12-mxp.live.mmcdn.com/live-hls/amlst:tifalock_-sd-e1970279f91af8fdb7b456032a88ae321e17e6e9d5b632fe0aa5722c7390fad0_trns_h264/chunklist_w30629623_b5128000_t64RlBTOjMwLjA=.m3u8', nom: 'tifalock_' },
        { url: 'https://live.viewsurf.com/liveedge/capdail01_live/chunklist.m3u8', nom: 'Caméra TEST' },
        { url: 'https://live.viewsurf.com/liveedge/frejus01_2_2/chunklist.m3u8', nom: 'Caméra Fréjus' }
    ];

    var indexCameraActuelle = 0;

    // Initialiser Artplayer avec la première caméra
    var art = initialiserArtplayer(cameras[indexCameraActuelle].url);
    document.getElementById('nomCamera').textContent = cameras[indexCameraActuelle].nom;
    verifierEtatFlux(cameras[indexCameraActuelle].url); // Vérifier l'état du flux initial

    // Gestionnaire d'événements pour le bouton "Caméras"
    document.querySelector('.btn-inner--text').addEventListener('click', function() {
        indexCameraActuelle = (indexCameraActuelle + 1) % cameras.length;
        var cameraSelectionnee = cameras[indexCameraActuelle];
        changerCamera(cameraSelectionnee.url, art);
        document.getElementById('nomCamera').textContent = cameraSelectionnee.nom;
        verifierEtatFlux(cameraSelectionnee.url); // Vérifier l'état du flux pour la nouvelle caméra
    });

    // Gestionnaire d'événements pour le bouton "ReSync"
    document.querySelector('.btn-inner--text--resync').addEventListener('click', function() {
        var cameraActuelle = cameras[indexCameraActuelle];
        rechargerCamera(cameraActuelle.url, art);
        verifierEtatFlux(cameraActuelle.url); // Vérifier à nouveau l'état du flux
    });
});

// Fonction pour initialiser Artplayer
function initialiserArtplayer(url) {
    return new Artplayer({
        container: '.artplayer-app',
        url: url,
        setting: true,
    plugins: [
        artplayerPluginHlsQuality({
            control: true,
            setting: true,
            getResolution: (level) => level.height + 'P',
            title: 'Quality',
            auto: 'Auto',
        }),
    ],
    customType: {
        m3u8: function playM3u8(video, url, art) {
            if (Hls.isSupported()) {
                if (art.hls) art.hls.destroy();
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(video);
                art.hls = hls;
                art.on('destroy', () => hls.destroy());
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = url;
            } else {
                art.notice.show = 'Unsupported playback format: m3u8';
            }
        }
    },
    poster: 'https://media.discordapp.net/attachments/1098691319217537044/1104871386176036874/Sergent78_Apocalyptic_Unicorn_f8409e6a-f6cd-4175-a32a-22902c05adee.png?width=1170&height=655',
    volume: 0.5,
    isLive: true,
    muted: false,
    autoplay: true,
    pip: true,
    autoSize: true,
    autoMini: true,
    screenshot: false,
    loop: true,
    flip: true,
    playbackRate: true,
    aspectRatio: false,
    fullscreen: true,
    fullscreenWeb: true,
    subtitleOffset: true,
    miniProgressBar: false,
    mutex: true,
    backdrop: true,
    playsInline: true,
    autoPlayback: true,
    airplay: true ,
    theme: '#23ade5',
    lang: navigator.language.toLowerCase(),
    moreVideoAttr: {
        crossOrigin: '*',
    },
    settings: [
        {
            width: 200,
            html: 'Subtitle',
            tooltip: 'Français',
            icon: '<img width="22" height="22" src="/assets/img/subtitle.svg">',
            selector: [
                {
                    html: 'Display',
                    tooltip: 'Show',
                    switch: true,
                    onSwitch: function (item) {
                        item.tooltip = item.switch ? 'Hide' : 'Show';
                        art.subtitle.show = !item.switch;
                        return !item.switch;
                    },
                },
                {
                    default: true,
                    html: 'Français',
                    url: '/assets/sample/subtitle.srt',
                },
            ],
            onSelect: function (item) {
                art.subtitle.switch(item.url, {
                    name: item.html,
                });
                return item.html;
            },
        },
        {
            html: 'Switcher',
            icon: '<img width="22" height="22" src="/assets/img/state.svg">',
            tooltip: 'OFF',
            switch: false,
            onSwitch: function (item) {
                item.tooltip = item.switch ? 'OFF' : 'ON';
                console.info('You clicked on the custom switch', item.switch);
                return !item.switch;
            },
        },
        {
            html: 'Slider',
            icon: '<img width="22" height="22" src="/assets/img/state.svg">',
            tooltip: '5x',
            range: [5, 1, 10, 0.1],
            onRange: function (item) {
                return item.range + 'x';
            },
        },
    ],
    contextmenu: [
        {
            html: 'Custom menu by MrKey2B',
            click: function (contextmenu) {
                console.info('You clicked on the custom menu');
                contextmenu.show = true;
            },
        },
    ],
    // layers: [
    //     {
    //         html: '<img width="100" src="https://media.discordapp.net/attachments/1098691319217537044/1104871386176036874/Sergent78_Apocalyptic_Unicorn_f8409e6a-f6cd-4175-a32a-22902c05adee.png?width=1170&height=655">',
    //         click: function () {
    //             window.open('#');
    //             console.info('custom layer');
    //         },
    //         style: {
    //             position: 'absolute',
    //             top: '20px',
    //             right: '20px',
    //             opacity: '.9',
    //         },
    //     },
    // ],
    quality: [
        {
            default: true,
            html: 'Source',
            url: url,
        },
    ],
    thumbnails: {
        url: '/assets/sample/thumbnails.png',
        number: 60,
        column: 10,
    },
    subtitle: {
        url: '/assets/sample/subtitle.srt',
        type: 'srt',
        style: {
            color: '#fe9200',
            fontSize: '20px',
        },
        encoding: 'utf-8',
    },
    highlight: [
        {
            time: 15,
            text: 'One more chance',
        },
        {
            time: 30,
            text: '??????????',
        },
        {
            time: 45,
            text: '?????????',
        },
        {
            time: 60,
            text: '???????????????',
        },
        {
            time: 75,
            text: '???',
        },
    ],
    icons: {
        loading: '<img width="150" height="150" src="https://media1.giphy.com/media/QWvNahpAca6J6K531c/giphy.gif">',
        state: '<img width="150" height="150" src="https://img.icons8.com/?size=100&id=SaiGysMETVHm&format=svg">',
        indicator: '<img width="16" height="16" src="/assets/img/indicator.svg">',
    },
});
}

// Change Cam
function changerCamera(urlCamera, art) {
    if (art.hls) {
        art.hls.destroy();
    }

    const hls = new Hls();
    hls.loadSource(urlCamera);
    hls.attachMedia(art.video);
    art.hls = hls;
    art.on('destroy', () => hls.destroy());
}
// ReSync Player
function rechargerCamera(urlCamera, art) {
    if (art.hls) {
        art.hls.destroy();
    }

    const hls = new Hls();
    hls.loadSource(urlCamera);
    hls.attachMedia(art.video);
    art.hls = hls;
    art.on('destroy', () => hls.destroy());
}

function verifierEtatFlux(urlCamera) {
    fetch(urlCamera, { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                // Le flux est accessible, mettre à jour avec bg-success
                document.querySelector('.btn-inner--icon .rounded-circle').classList.remove('bg-danger');
                document.querySelector('.btn-inner--icon .rounded-circle').classList.add('bg-success');
            } else {
                // Le flux n'est pas accessible, mettre à jour avec bg-danger
                document.querySelector('.btn-inner--icon .rounded-circle').classList.remove('bg-success');
                document.querySelector('.btn-inner--icon .rounded-circle').classList.add('bg-danger');
            }
        })
        .catch(error => {
            // En cas d'erreur (flux non accessible), mettre à jour avec bg-danger
            document.querySelector('.btn-inner--icon .rounded-circle').classList.remove('bg-success');
            document.querySelector('.btn-inner--icon .rounded-circle').classList.add('bg-danger');
        });
}



</script>
</x-app-layout>
