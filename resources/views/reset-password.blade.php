<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Reset Password | {{ env('APP_NAME') }}</title>

  <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css" rel="stylesheet">
  <script defer src="https://use.fontawesome.com/releases/v5.14.0/js/all.js"></script>

  <style>
    html,body {
      margin: 0;
      padding: 0;
      background-color: #f3f3f3;
    }

    .center {
      width: 50%;
      margin: 0 auto;
    }

    @media screen and (max-width: 960px) {
      .center {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div id="app">
    <section class="section">
      <div class="container">
        {{-- Alert --}}
        <div v-if="alert.message !== null" class="notification is-success center mb-3" :class="{'is-danger': alert.error}">
          <template v-if="alert.error">
            <span>
              <i class="fas fa-exclamation-circle"></i>
            </span>
          </template>

          <template v-else>
            <span>
              <i class="fas fa-check-circle"></i>
            </span>
          </template>

          <span class="ml-1 has-text-weight-medium">@{{ alert.message }}</span>
        </div>
        
        {{-- The Card --}}
        <div class="card center">
          <div class="card-content">
            {{-- Header --}}
            <p class="subtitle mb-3">
              <i class="fa fa-lock"></i>
              <span class="ml-1">Reset Password</span>
            </p>
  
            {{-- The Form --}}
            <form @submit.prevent="resetPassword">
              {{-- Email --}}
              <div class="field">
                <label for="email" class="label">Email</label>
                <div class="control has-icons-left">
                  <input id="email" type="email" class="input" value="{{ $email }}" disabled>
                  <span class="icon is-left">
                    <i class="fas fa-envelope"></i>
                  </span>
                </div>
              </div>

              {{-- Password --}}
              <div class="field">
                <label for="password" class="label">New Password</label>
                <div class="control has-icons-left">
                  <input id="password" v-model="form.password" type="password" class="input" :class="{'is-danger': error}" placeholder="Password">
                  <span class="icon is-left">
                    <i class="fas fa-lock"></i>
                  </span>
                </div>
  
                <p v-if="error" class="help is-danger">@{{ error }}</p>
              </div>

              {{-- Password Confirmation --}}
              <div class="field">
                <label for="password_confirmation" class="label">Password Confirmation</label>
                <div class="control has-icons-left">
                  <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="input" placeholder="Password Confirmation">
                  <span class="icon is-left">
                    <i class="fas fa-lock"></i>
                  </span>
                </div>
              </div>

              {{-- Submit Button --}}
              <button type="submit" class="mt-2 button is-info is-fullwidth" :class="{'is-loading': loading}" :disabled="loading || finish">Reset Password</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.0/axios.min.js" integrity="sha512-DZqqY3PiOvTP9HkjIWgjO6ouCbq+dxqWoJZ/Q+zPYNHmlnI2dQnbJ5bxAHpAMw+LXRm4D72EIRXzvcHQtE8/VQ==" crossorigin="anonymous"></script>
  <script>
    const app = new Vue({
      el: '#app',
      data: {
        form: {
          password: null,
          password_confirmation: null,
        },
        error: null,
        alert: {
          message: null,
          error: false
        },
        loading: false,
        finish: false,
      },
      methods: {
        async resetPassword() {
          this.loading = true
          this.error = null
          this.alert = {
            message: null,
            error: false
          }

          try {
            await axios.post("{{ route('reset_password') }}", {
              ...this.form,
              _method: 'PATCH',
              email: "{{ $email }}",
              token: "{{ $token }}",
            })

            this.finish = true
            this.alert = {
              message: 'Password updated.'
            }
            this.form = {
              password: null,
              password_confirmation: null,
            }
          } catch (err) {
            const errCode = err?.response?.status

            if (errCode === 422) {
              this.error = err.response.data.password[0]
            } else {
              this.alert = {
                error: true,
                message: err?.response?.data?.message || 'Failed to reset user\'s password.'
              }
            }
          } finally {
            this.loading = false
          }
        }
      }
    })
  </script>
</body>
</html>