document.addEventListener("DOMContentLoaded", function() {
    const containerDiv = document.getElementById('container');
    const userBtn = document.getElementById('userBtn');
    const sendBtn = document.getElementById('sendBtn');
    const message = document.getElementById('message');
    const chatBox = document.getElementById('chatbox');
    const searchBox = document.getElementById('search-box');
    const searchBtn = document.getElementById('searchBtn');
    const returnBtn = document.getElementById('returnBtn');
    const chatListDiv = document.getElementById('chatList');
    const chatAreaDiv = document.getElementById('chatArea');
    const chatNameDiv = document.getElementById('chatName');
    const welcomeDiv = document.getElementById('welcome');

    storeData();
    const serverIp = JSON.parse(sessionStorage.getItem('serverIp')).url;
    const startChatURL = `${serverIp}/conversation/start-chat`;
    const createChatURL = `${serverIp}/create-chat`;
    const searchUserURL = `${serverIp}/conversation/search-user`;
    const searchUserByIdURL = `${serverIp}/conversation/search-userById`;
    const sendMessageURL = `${serverIp}/send-notification`;
    const storeMessageURL = `${serverIp}/conversation/send-message`;

    async function sendMessage(data) {
        console.log('start sending message');

        console.log(sendMessageURL);
        console.log(data);
        console.log(JSON.stringify(data));

        try{
            const response = await fetch(sendMessageURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })

            if(!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            } else {
                console.log('send message successfully');
            }
        }
        catch (error){
            console.error('Error:', error);
        }

        const json = {
            userId: data.sender.id,
            chatId: data.receiver.id,
            groupId: 0,
            type: data.type,
            content: data.content,
            sentAt: data.sentAt,
            deliveredAt: '',
            seenAt: ''
        };

        console.log(storeMessageURL);
        console.log(json);
        console.log(JSON.stringify(json));

        try {
            const response = await fetch(storeMessageURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(json)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            } else {
                console.log('store message successfully');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    const maxChars = 200;
    message.addEventListener('input', function(event) {
            if (message.innerText.length > maxChars) {
            alert('Error: Message content cannot be more than 200 chars.');
            message.innerText = message.innerText.substr(0, maxChars);
            const range = document.createRange();
            const sel = window.getSelection();
            range.selectNodeContents(message);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    });

    message.addEventListener('keypress',function (event){
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            sendBtn.click();
        }
    });

    sendBtn.addEventListener('click', async function () {
        const messageContent = message.textContent.trim();

        if (!messageContent) {
            console.error('Error: Message content cannot be empty.');
            alert('Error: Message content cannot be empty.');
            return;
        }

        message.innerText = ``;

        const user = JSON.parse(sessionStorage.getItem('user'));
        const receiver = JSON.parse(sessionStorage.getItem('chat'));
        const data = {
            type: 'message',
            sender: {
                'id': user.id,
                'username': user.username,
                'email': user.email
            },
            receiver: {
                'id': receiver.id,
                'chatId': receiver.chatId,
                'username': receiver.username,
                'email': receiver.email
            },
            content: messageContent,
            sentAt: getCurrentFormattedTime()
        };

        addMessageToChatbox(data);
        storeMessage(data);
        // ws.send(JSON.stringify(data));
        await sendMessage(data);
    });

    searchBox.addEventListener('keypress', function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchBtn.click();
        }
    });

    searchBtn.addEventListener('click',async function () {
        const messageContent = searchBox.value.trim();

        if (!messageContent) {
            console.error('Error: SearchBox content cannot be empty.');
            alert('Error: SearchBox content cannot be empty.');
            return;
        }

        returnBtn.style.display = 'block';

        const username = searchBox.value;
        const data = {
            username: username
        }

        try {
            const response = await fetch(searchUserURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }

            console.log('start await');
            let responseText;
            try {
                responseText = await response.text();
                console.log(responseText);
                const json = JSON.parse(responseText);
                console.log(json);
                displayChatList(json);
            } catch (jsonError) {
                console.error('Failed to parse JSON:', jsonError);
                console.error('Response text:', responseText);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    returnBtn.addEventListener('click', function(event){
        returnBtn.style.display = 'none';
        // displayTheOriginalUserList
        const chatList = JSON.parse(sessionStorage.getItem('chatList'));
        if(!(Array.isArray(chatList) && chatList.length === 0)){
            displayChatList(chatList);
        }
        else{
            const div = document.getElementById('chatList');
            div.innerHTML = ``;
        }
        searchBox.value = '';
    });

    addChatButtonsListener();

    function displayChatList(data){
        if(!(Array.isArray(data) && data.length === 0)){
            const div = document.getElementById('chatList');
            div.innerHTML = ``;
            for(let i=0;i<data.length;i++){
                console.log("data[i].userId: " + data[i].userId);
                console.log(data[i].userId);

                const userId = data[i].userId ? data[i].userId : data[i].id;
                let chatId ;
                if(data[i].userId){
                    chatId = data[i].id;
                }
                else{
                    chatId = foundChatIdByUserId(userId);
                }

                const chatData = {
                    'id': chatId,   //it will be 0, if this is a new chat. The chatId will be put in after user click on the chat button
                    'userId': userId,
                    'username': data[i].username,
                    'email': data[i].email,
                    'icon': data[i].icon
                }
                console.log(chatData);
                if(userId !== JSON.parse(sessionStorage.getItem('user')).id){
                    const html = `
                    <button class="borderless-button chat-button" data-chat="${JSON.stringify(chatData).replace(/"/g, '&quot;')}">
                        ${chatData.icon ?
                        `<img class="user-image" src="data:image/png;base64,${chatData.icon}" alt="user${chatData.id}" width="35" height="35">` :
                        `<img class="user-image" src="/public/images/user_icon.png" alt="user${chatData.id}" width="35" height="35">`
                    }
                        <span class="user-name">${chatData.username}</span>
                        <span class="notification-dot"></span>
                    </button>
                `
                    div.innerHTML+=html;
                }
            }
            addChatButtonsListener();
        }
        else{
            const div = document.getElementById('chatList');
            div.innerHTML = `
                <button class="borderless-button chat-button" id="0">
                    <img class="user-image" src="/public/images/user_icon.png" alt="user{{ chat.id }}" width="35" height="35">
                    <span class="user-name">User didn't found</span>
                </button>
            `;
        }
    }

    function displayMessages(data){
        const messages = JSON.parse(sessionStorage.getItem('messages'));
        const user = JSON.parse(sessionStorage.getItem('user'));
        const chatList = JSON.parse(sessionStorage.getItem('chatList'));
        const chat = JSON.parse(sessionStorage.getItem('chat'));
        let sender;
        for(let i=0;i<messages.length;i++){
            if(messages[i].chatId === chat.id){
                if (messages[i].userId === user.id) {
                    sender = {
                        id: messages[i].userId,
                        username: user.username,
                    }
                } else {
                    chatList.forEach(chat => {
                        if (messages[i].userId === chat.userId) {
                            sender = {
                                id: messages[i].userId,
                                username: chat.username
                            }
                        }
                    })
                }

                const data = {
                    sender: sender,
                    content: messages[i].content
                }
                addMessageToChatbox(data);
            }
        }
    }

    function addMessageToChatbox(data) {
        const messageElement = document.createElement('div');
        console.log();
        if(data.sender.id === JSON.parse(sessionStorage.getItem('user')).id){
            const user = JSON.parse(sessionStorage.getItem('user'));
            messageElement.className = 'cright cmsg';
            messageElement.innerHTML = `
                ${user.icon ?
                `<img class="headIcon radius" src="data:image/png;base64,${user.icon}" ondragstart="return false;" oncontextmenu="return false;">` :
                `<img class="headIcon radius" src="/public/images/user_icon.png" ondragstart="return false;" oncontextmenu="return false;">`
                }
                <span class="name">
                    <span>${data.sender.username}</span>
                </span>
                <span class="content">${data.content}</span>
            `;
        }
        else {
            const chat = JSON.parse(sessionStorage.getItem('chat'));
            messageElement.className = 'cleft cmsg';
            messageElement.innerHTML = `
                ${chat.icon ?
                `<img class="headIcon radius" src="data:image/png;base64,${chat.icon}" ondragstart="return false;" oncontextmenu="return false;">` :
                `<img class="headIcon radius" src="/public/images/user_icon.png" ondragstart="return false;" oncontextmenu="return false;">`
            }
                <span class="name">
                    <span>${data.sender.username}</span>
                </span>
                <span class="content">${data.content}</span>
            `;
        }
        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function notify(data) {
        const buttons = document.querySelectorAll('.chat-button');
        buttons.forEach(button => {
            if (JSON.parse(button.getAttribute('data-chat')).userId === data.sender.id) {
                const notificationDot = button.querySelector('.notification-dot');
                notificationDot.style.display = 'block';
            }
        });
    }

    function storeData(){
        // data we need to update in sessionStorage
        // 1. user
        // 2. chatList
        // 3. serverIp
        // 4. chat (After user select a chat)
        // 5. message (after user send, receive a message)
        const chatList = JSON.parse(chatListDiv.getAttribute('data-chatList'));
        const user = JSON.parse(userBtn.getAttribute('data-user'));
        const serverIp = JSON.parse(containerDiv.getAttribute('data-server-ip'));
        const webSocketIp = JSON.parse(document.querySelector('meta[name="webSocketIp"]').getAttribute('data-webSocketIp'));
        const messages = JSON.parse(document.querySelector('meta[name="messages"]').getAttribute('data-messages'));
        const chat = [];
        sessionStorage.setItem('chatList', JSON.stringify(chatList));
        sessionStorage.setItem('user', JSON.stringify(user));
        sessionStorage.setItem('serverIp', JSON.stringify(serverIp));
        sessionStorage.setItem('webSocketIp', JSON.stringify(webSocketIp));
        sessionStorage.setItem('messages', JSON.stringify(messages));
        sessionStorage.setItem('chat', JSON.stringify(chat));

        console.log('start pusher');
        var pusher = new Pusher("49f29964ce5947e127d8", {
            cluster: "eu"
        });
        console.log('end pusher');
        const channelName = "my-channel-" + user.id;
        const channel = pusher.subscribe(channelName);
        channel.bind('createChat', async function (data) {
            console.log('receive ' + data);
            // location.reload();
            try {
                const id = data.userId1;
                const user = {
                    id: id
                }
                console.log(data.userId1);
                console.log(user);
                const response = await fetch(searchUserByIdURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(user)
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }

                let responseText;
                try {
                    responseText = await response.text();
                    console.log(responseText);
                    const json = JSON.parse(responseText);
                    console.log(json);

                    const chat = {
                        'id': data.chatId,
                        'userId': json[0].id,
                        'username': json[0].username,
                        'email': json[0].email,
                        'icon': json[0].icon
                    }
                    console.log(chat);
                    const chatList = JSON.parse(sessionStorage.getItem('chatList'));
                    chatList.push(chat);
                    sessionStorage.setItem('chatList', JSON.stringify(chatList));
                    displayChatList(JSON.parse(sessionStorage.getItem('chatList')));

                    const channel = 'my-channel-' + chat.id;
                    bindChannel(channel);
                } catch (jsonError) {
                    console.error('Failed to parse JSON:', jsonError);
                    console.error('Response text:', responseText);
                    console.error('chatList:', JSON.parse(sessionStorage.getItem('chatList')))
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
        console.log('bind to ' + channelName);

        chatList.forEach(chat => {
            const channel = 'my-channel-' + chat.id;
            bindChannel(channel);
        });
    }

    function storeMessage(data){
        const message = {
            userId: data.sender.id,
            chatId: data.receiver.id,
            groupId: 0,
            type: 'message',
            content: data.content,
            sentAt: '',
            deliveredAt: '',
            seenAt: ''
        }
        let messages = JSON.parse(sessionStorage.getItem('messages'));
        messages.push(message);
        sessionStorage.setItem('messages', JSON.stringify(messages));
    }

    function addChatButtonsListener() {
        console.log('button listener start');
        const buttons = document.querySelectorAll('.chat-button');
        buttons.forEach(button => {
            button.addEventListener('click', async function () {
                welcomeDiv.style.display = 'none';
                if(JSON.parse(this.getAttribute('data-chat')).id === 0 && foundChatIdByUserId(JSON.parse(this.getAttribute('data-chat')).userId) === 0){
                    // if this chat is not in the chatList, we need to add it to database
                    const data = {
                        'userId1': JSON.parse(sessionStorage.getItem('user')).id,
                        'userId2': JSON.parse(this.getAttribute('data-chat')).userId
                    };

                    console.log(JSON.parse(this.getAttribute('data-chat')).userId);

                    try{
                        const response = await fetch(startChatURL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        if (!response.ok) {
                            console.log(response);
                            throw new Error('Network response was not ok ' + response.statusText);
                        }

                        let json = await response.json();
                        const data_createChat = {
                            chatId: json.chatId,
                            userId1: data.userId1,
                            userId2: data.userId2
                        }

                        const response_createChat = await fetch(createChatURL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data_createChat)
                        })

                        if(!response_createChat.ok) {
                            console.log(response_createChat);
                            throw new Error('Network response was not ok ' + response_createChat.statusText);
                        }

                        //update data-chat of button (update chatId)
                        const chat = JSON.parse(this.getAttribute('data-chat'));
                        console.log(json);
                        console.log(json.chatId);
                        chat.id = json.chatId;
                        this.setAttribute('data-chat', JSON.stringify(chat));

                        //update chat into chatList locally
                        const chatList = JSON.parse(sessionStorage.getItem('chatList'));
                        chatList.push(chat);
                        sessionStorage.setItem('chatList', JSON.stringify(chatList));

                        const channelName = 'my-channel-' + chat.id;
                        bindChannel(channelName);
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }

                //change the header of chatBox
                chatBox.innerText = '';
                const chat = JSON.parse(this.getAttribute('data-chat'));
                chatNameDiv.innerText = chat.username;
                chatAreaDiv.style.display = 'flex';

                //not display notification
                const notificationDot = button.querySelector('.notification-dot');
                notificationDot.style.display = 'none';

                //update chat locally
                sessionStorage.setItem('chat', JSON.stringify(chat));

                if(isMessagesInSession(chat.id)){
                    const messages = JSON.parse(sessionStorage.getItem('messages'));
                    displayMessages(messages);
                }
            });
        });
    }

    function isMessagesInSession(chatId){
        let flag = false;
        const messages = JSON.parse(sessionStorage.getItem('messages'));
        for(let i=0;i<messages.length;i++){
            if(Number(messages[i].chatId) === Number(chatId)){
                flag = true;
                break;
            }
        }
        return flag;
    }

    function getCurrentFormattedTime() {
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    function foundChatIdByUserId(id){
        let chatId = 0;
        const chatList = JSON.parse(sessionStorage.getItem('chatList'));
        for(let i=0;i<chatList.length;i++){
            if(chatList[i].userId === id){
                chatId = chatList[i].id;
                break;
            }
        }
        return chatId;
    }

    function bindChannel(channelName){
        console.log('start pusher');
        var pusher = new Pusher("49f29964ce5947e127d8", {
            cluster: "eu"
        });
        console.log('end pusher');
        const channel = pusher.subscribe(channelName);
        channel.bind('message', async function (data) {
            const message = data;
            console.log('receive' + message);
            if (message.sender.id === JSON.parse(sessionStorage.getItem('chat')).userId) {
                console.log('receive');
                addMessageToChatbox(message);
                storeMessage(message);
            } else if(message.sender.id !== JSON.parse(sessionStorage.getItem('user')).id){
                console.log('receive else');
                console.log(message.sender.id);
                console.log(JSON.parse(sessionStorage.getItem('chat')).userId);
                console.log('receive from websocket a message(sender_id != chat.userId)');
                await notify(message);
                storeMessage(message);
            }
        });
        console.log('bind to ' + channelName);
    }
});