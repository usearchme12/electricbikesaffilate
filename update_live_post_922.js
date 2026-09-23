const https = require('https');
const fs = require('fs');

const auth = Buffer.from('reightgoodbikes:Bglr kvon PDKG h4ww LrLK QO55').toString('base64');
const content = fs.readFileSync('new_post_922_content.html', 'utf8');

const postData = JSON.stringify({
  content: content
});

const options = {
  hostname: 'reightgoodbikes.co.uk',
  port: 443,
  path: '/wp-json/wp/v2/posts/922',
  method: 'POST',
  headers: {
    'Authorization': 'Basic ' + auth,
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(postData)
  }
};

const req = https.request(options, (res) => {
  let responseBody = '';
  res.on('data', (chunk) => {
    responseBody += chunk;
  });
  res.on('end', () => {
    console.log('HTTP Status:', res.statusCode);
    if (res.statusCode >= 200 && res.statusCode < 300) {
      const data = JSON.parse(responseBody);
      console.log('Successfully updated post ID:', data.id);
      console.log('Link:', data.link);
      console.log('Modified:', data.modified);
    } else {
      console.error('Update failed:', responseBody);
    }
  });
});

req.on('error', (e) => {
  console.error('Request error:', e);
});

req.write(postData);
req.end();
