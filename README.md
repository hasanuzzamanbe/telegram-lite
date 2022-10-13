# post-on-telegram
A WordPress Plugin to send message on telegram channel/group when new post published.


== User guide ==

1. Create a BOT from @botfather
2. Generate BOT Token from @botfather
3. Create a Channel and add your BOT as admin
4. Send a test message on channel and forward that message to @jsondumpbot
5. Collect ChatId from "forward_from_chat" section of JSON
  Example: "id": -1001xxxxxx45,


When you create a new post it will automatically publish on your channel.