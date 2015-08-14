upp = new Array('','A','B','C','D','E','F','G','H','I','J','K','L',
    'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
low = new Array('','a','b','c','d','e','f','g','h','i','j','k','l',
    'm','n','o','p','q','r','s','t','u','v','w','x','y','z');
dig = new Array('','0','1','2','3','4','5','6','7','8','9');
spec = new Array('','!','$','&','*','?','#');

function rnd(x,y,z) {
 var num;
 do {
    num = parseInt(Math.random()*z);
    if (num >= x && num <= y) break;
 } while (true);
return(num);
}

function gen_pass() {
var pswrd = '';
var znak, s;
var k = 0;
var n = document.pass_form.numbers.value;
var pass = new Array();
var w = rnd(30,80,100);
for (var r = 0; r < w; r++) {
    if (pass_form.upper.checked) { znak = rnd(1,26,100); pass[k] = upp[znak]; k++; }
    if (pass_form.lower.checked) { znak = rnd(1,26,100); pass[k] = low[znak]; k++; }
    if (pass_form.digit.checked) { znak = rnd(1,10,100); pass[k] = dig[znak]; k++; }
    if (pass_form.spec.checked) { znak = rnd(1,6,100); pass[k] = spec[znak]; k++; }
}
for (var i = 0; i < n; i++) {
    s = rnd(1,k-1,100);
    pswrd+= pass[s];
}
document.pass_form.getback.value = pswrd;
}

function generate() {
if (pass_form.upper.checked||pass_form.lower.checked||pass_form.digit.checked)
    { gen_pass(); }
else { alert('Выберите тип символов!'); pass_form.upper.checked = true; }
}