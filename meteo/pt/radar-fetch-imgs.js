const fs = require('fs')
const request = require('request')
const sharp = require('sharp');

const radarURL = 'http://www.ipma.pt/pt/otempo/obs.radar/'
const preCodeBlock = '<select class="spaceT2B8" name="cmbSelFich" id="Imagem" title="Imagem" onchange="javascript: processFileCombo(this);" >'
const postCodeBlock = '</select>'

// <option value="20201023/XEfVGDigLqqYazMSwVNe/por201023204013.jpg">2020-10-23 20:40h</option>
const preImageString = '<option value="'
const preLabelString = '">'
const postLabelString = '</option>'

const preURL = 'https://www.ipma.pt/resources.www/data/observacao/radar/imagens/'
const localPath = './radar/'

// get radar html
request(radarURL, function (error, response, body) {
    console.log('start request get radar html')
    if (!error) {
        parseRadarHtml(body);
    } else {
        console.log(error);
    }
});

// download radar img
const download = (url, path, callback) => {
    request.head(url, (err, res, body) => {
        request(url)
        .pipe(fs.createWriteStream(path))
        .on('close', callback)
    })
}

// parse radar html and get image URLs
var parseRadarHtml = function(html) {
    console.log('start parseRadarHtml')

    var json = {
        status: "ok",
        images: []
    }

    if (html.includes(preCodeBlock)) {
        var initialIndex = html.indexOf(preCodeBlock)

        html = html.substring(initialIndex + preCodeBlock.length, html.length)

        if (html.includes(postCodeBlock)) {
            var endIndex = html.indexOf(postCodeBlock)
            html = html.substring(0, endIndex)
        }
    }

    // here, html should be an array of <option value="20201023/XEfVGDigLqqYazMSwVNe/por201023204013.jpg">2020-10-23 20:40h</option>

    let htmlLines = html.split("\n")
    var imgURLs = []
    var imgLabels = []
    htmlLines.forEach(addItemToArray)

    function addItemToArray(value) {
        if (value.includes(preImageString) && value.includes(preLabelString) && value.includes(postLabelString)) {            
            value = value.trim()

            let imgURL = value.substring(preImageString.length, value.indexOf(preLabelString))
            imgURLs.push(imgURL)

            let imgLabel = value.substring(value.indexOf(preLabelString)+preLabelString.length, value.indexOf(postLabelString))
            imgLabels.push(imgLabel)
        }
    }

    // here, imgURLs and imgLabels should be filled in
    console.log("item one is " + imgLabels[0] + "; " + imgURLs[0])

    // download images
    imgURLs.forEach(downloadImages)
    function downloadImages(value, index) {
        if (index < 5) {
            let url = preURL + value
            let path = localPath + index + ".raw.jpg"
            download(url, path, () => {
                let outputImage = path.replace('.raw', '');

                sharp(path).extract({ width: 479, height: 600, left: 0, top: 0 }).toFile(outputImage)
                    .then(function(new_file_info) {
                        fs.unlink(path, (err) => {})

                        //console.log("add new json item")
                        let time = imgLabels[index].substring(imgLabels[index].indexOf(" ")+1, imgLabels[index].length)
                        json.images.push({index: ""+index, time: time, dateTime: imgLabels[index], imageName: outputImage.replace(localPath, ''), imageUrl: ""})
                        writeJson()
                    })
                    .catch(function(err) {
                        console.log("An error occured: " + err)
                    });
            })
        }

    }

    function writeJson() {
        if (json.images.length == 5) {
            var jsonFile = JSON.stringify(json)
            fs.writeFileSync(localPath + 'radar.json', jsonFile);
        }
    }
    

};
