<template>
  <div class="row">
    <div class="col-8">
      <div class="card card-default">
        <div class="card-header">Messages</div>
        <div class="card-body p-0">
          <ul class="list-unstyled" style="height: 300px; overflow-y: scroll">
            <li class="p-2" v-for="(message, index) in messages" :key="index">
              <strong>{{ message.user.name }}</strong>
              {{ message.message }}
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
        />
      </div>
      <span class="text-muted"> user is typing...</span>
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

    onMounted(() => {
      fetchMessages();
    });

    window.Echo.join("channel-chat")
      .here((user) => {
        users.value = user;
      })
      .joining((user) => {
        users.value.push(user);
      })
      .leaving((user) => {
        users.value.filter((u) => u.id != user.id);
      })
      .listen(".SendMessage", (e) => {
        console.log(e);
        messages.value.push({
          user: e.user,
          message: e.message.message,
        });
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

    return {
      messages,
      users,
      newMessage,
      sendMessage,
    };
  },
};
</script>
