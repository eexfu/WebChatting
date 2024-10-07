document.getElementById('course-schedule').addEventListener('click', function() {
    document.getElementById('content').innerHTML = `
        <h2>Course Schedule</h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Time</th>
            </tr>
            {% for title in course_titles %}
                <tr>
                    <td>{{ title }}</td>
                    <td>2024-5-7 18:00</td>
                </tr>
            {% endfor %}
        </table>
    `;
});

document.getElementById('groups').addEventListener('click', function() {
    const div = document.getElementById('content');
    const groupList = JSON.parse(div.getAttribute('data-groupList'));

    div.innerHTML = `
        <button class="borderless-button">
            <img class="user-image" src="/public/images/user_icon.png" alt="User" width="35" height="35">
            <span class="user-name">groupname</span>
        </button>
        <button class="borderless-button">
            <img class="user-image" src="/public/images/user_icon.png" alt="User" width="35" height="35">
            <span class="user-name">groupname</span>
        </button>
        <button class="borderless-button">
            <img class="user-image" src="/public/images/user_icon.png" alt="User" width="35" height="35">
            <span class="user-name">groupname</span>
        </button>
    `;
});

document.getElementById('feedback').addEventListener('click', function() {
    document.getElementById('content').innerHTML = `
        <h2> Give Feedback </h2>

        <div class="lite-chatbox my-chatbox" id="chatbox">

        </div>

        <div class="lite-chattools">
            <div class="lite-chatbox-tool" id="emojiMart" style="display:none">
                <em-emoji-picker></em-emoji-picker>
            </div>
            <div id="toolMusk" style="display:none">

            </div>
        </div>

        <div class="lite-chatinput my-chatinput">
            <hr class="boundary" style="margin-bottom: 20px">
            <div class="editor chatinput" aria-label="input area" contenteditable="true" ref="editor" id="message" style="margin-bottom: 10px"></div>
            <button class="send" id="sendBtn">Send</button>
            <div style="margin-bottom: 20px"></div>
            <hr class="boundary">
        </div>
    `;
});
