//留白
var whiteImg = 'data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QMraHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjMtYzAxMSA2Ni4xNDU2NjEsIDIwMTIvMDIvMDYtMTQ6NTY6MjcgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzYgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjk2QjIxRjMxMDY4RDExRUE5NEYyRUI4OUU5NEFDQzE5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjk2QjIxRjMyMDY4RDExRUE5NEYyRUI4OUU5NEFDQzE5Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OTZCMjFGMkYwNjhEMTFFQTk0RjJFQjg5RTk0QUNDMTkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OTZCMjFGMzAwNjhEMTFFQTk0RjJFQjg5RTk0QUNDMTkiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7/7gAOQWRvYmUAZMAAAAAB/9sAhAAGBAQEBQQGBQUGCQYFBgkLCAYGCAsMCgoLCgoMEAwMDAwMDBAMDg8QDw4MExMUFBMTHBsbGxwfHx8fHx8fHx8fAQcHBw0MDRgQEBgaFREVGh8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx//wAARCAAIAxUDAREAAhEBAxEB/8QASwABAQAAAAAAAAAAAAAAAAAAAAgBAQAAAAAAAAAAAAAAAAAAAAAQAQAAAAAAAAAAAAAAAAAAAAARAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AKpAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB//2Q==';
//打印
function printOut(){
    html2canvas(document.querySelector('#printId'), {
        allowTaint: true,
        scale: 2 // 提升画面质量，但是会增加文件大小
    }).then(function (canvas) {
        /**jspdf将html转为pdf一页显示不截断，整体思路：
         * 1. 获取DOM 
         * 2. 将DOM转换为canvas
         * 3. 获取canvas的宽度、高度（稍微大一点） 
         * 4. 将pdf的宽高设置为canvas的宽高
         * 5. 将canvas转为图片
         * 6. 实例化jspdf，将内容图片放在pdf中（因为内容宽高和pdf宽高一样，就只需要一页，也防止内容截断问题）841.89
         */

        // 得到canvas画布的单位是px 像素单位
        var contentWidth = canvas.width
        var contentHeight = canvas.height
        // 将canvas转为base64图片
        var pageData = canvas.toDataURL('image/jpeg', 1.0)

        // 设置pdf的尺寸，pdf要使用pt单位 已知 1pt/1px = 0.75   pt = (px/scale)* 0.75
        // 2为上面的scale 缩放了2倍
        var pdfX = contentWidth / 2 * 0.75
        var pdfY = contentHeight/ 2 * 0.75 

        //页面留白
        var whiteHeight = 60;

        //一张pdf尺寸
        var pdfW = 595.32;
        var pdfH = 841.89
        
        // 设置内容图片的尺寸，img是pt单位 
        var imgX = pdfX;
        var imgY = (contentHeight / 2 * 0.75); 
        var imgH = pdfH - whiteHeight*2;//内容区高度
        //偏移量
        var position = whiteHeight;
        var pageTempHeight = (contentHeight / 2 * 0.75);
        // 初始化jspdf 第一个参数方向：默认''时为纵向，第二个参数设置pdf内容图片使用的长度单位为pt，第三个参数为PDF的大小，单位是pt
        var PDF = new jsPDF('', 'pt', 'a4');
        if(imgY<imgH){
            PDF.addImage(whiteImg, 'jpeg', 0, 0, pdfW, whiteHeight)
            PDF.addImage(pageData, 'jpeg', 0, 0, imgX, imgH)
            PDF.addImage(whiteImg, 'jpeg', 0, (imgH+whiteHeight), pdfW, whiteHeight)
        }else{
            while(pageTempHeight>0){
                //内容区
                PDF.addImage(pageData, 'jpeg', 0, position, imgX, imgY)
                //头部留白
                PDF.addImage(whiteImg, 'jpeg', 0, 0, pdfW, whiteHeight)
                //底部留白
                PDF.addImage(whiteImg, 'jpeg', 0, (imgH+whiteHeight+1), pdfW, whiteHeight)
                position -= imgH;
                pageTempHeight -= imgH;
                if(pageTempHeight>0){
                    PDF.addPage();
                }
            }
        }
        // 将内容图片添加到pdf中，因为内容宽高和pdf宽高一样，就只需要一页，位置就是 0,0
        
        PDF.save('download.pdf')
    })

}