<template>
  <div class="row">
    <div class="col-8">
      <div class="card card-default">
        <div class="card-header">Messages</div>
        <div class="card-body p-0">
          <ul
            class="list-unstyled"
            style="height: 300px; overflow-y: scroll"
            ref="hasScrolledToBottom"
          >
            <li class="p-2" v-for="(message, index) in messages" :key="index">
              <div
                class="message message-receive"
                v-if="user.id != message.user.id"
                :key="message.id"
              >
                <p>
                  <strong class="primary-font">
                    {{ message.user.name }}:
                  </strong>
                  {{ message.message }}
                </p>
              </div>
              <div class="message message-send" v-else :key="index">
                <p>
                  <strong class="primary-font">
                    {{ message.user.name }}:
                  </strong>
                  {{ message.message }}
                </p>
              </div>
            </li>
          </ul>
        </div>

        <input
          type="text"
          class="form-control"
          placeholder="Enter your message"
          name="message"
          v-model="newMessage"
          @keyup.enter="sendMessage"
          @keydown="keyTypingEvent"
        />
      </div>
      <span class="text-muted" v-if="activeUser">
        {{ activeUser.name }} is typing...</span
      >
    </div>
    <div class="col-4">
      <div class="card card-default">
        <div class="card-header">Active Users(online)</div>
        <div class="card-body">
          <ul>
            <li class="py-2" v-for="(user, index) in users" :key="index">
              {{ user.name }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUpdated } from "vue";
import axios from "axios";

export default {
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let messages = ref([]);
    let newMessage = ref("");
    let users = ref([]);
    let hasScrolledToBottom = ref("");
    let activeUser = ref(false);
    let typingTimer = false;

    onMounted(() => {
      fetchMessages();
    });

    onUpdated(() => {
      scrollBottom();
    });

    Echo.join("channel-chat")
      .here((user) => {
        users.value = user;
      })
      .joining((user) => {
        users.value.push(user);
      })
      .leaving((user) => {
        users.value = users.value.filter((u) => u.id != user.id);
      })
      .listen(".SendMessage", (e) => {
        messages.value.push({
          user: e.user,
          message: e.message.message,
        });
      })
      .listenForWhisper("typing", (res) => {
        activeUser.value = res;

        if(typingTimer) {
            clearTimeout(typingTimer)
        }

        let typingTimer = setTimeout(() => {
          activeUser.value = false;
        }, 1000);
      });

    const fetchMessages = async () => {
      await axios.get("/messages").then((response) => {
        messages.value = response.data;
      });
    };

    const sendMessage = async () => {
      let userMessage = {
        user: props.user,
        message: newMessage.value,
      };

      messages.value.push(userMessage);
      await axios
        .post("/send-message", userMessage)
        .then((response) => {
          return response;
        })
        .catch((err) => {
          console.error(err);
        });

      newMessage.value = "";
    };

    const scrollBottom = () => {
      if (messages.value.length > 1) {
        let el = hasScrolledToBottom.value;
        el.scrollTop = el.scrollHeight;
      }
    };

    const keyTypingEvent = () => {
      Echo.join("channel-chat").whisper("typing", props.user);
    };

    return {
      messages,
      users,
      newMessage,
      sendMessage,
      hasScrolledToBottom,
      keyTypingEvent,
      activeUser,
    };
  },
};
</script>
