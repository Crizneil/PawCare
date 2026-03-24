import os
import sys
import argparse
import subprocess
import psutil
from datetime import datetime
from rich.console import Console
from rich.panel import Panel
from rich.text import Text
import zipfile
import socket
import json
import time
import requests
import threading
try:
    import pyautogui
except ImportError:
    pyautogui = None
from github import Auth, Github
from dotenv import load_dotenv

load_dotenv()

try:
    import pyperclip
except ImportError:
    pyperclip = None

class GitCenter:
    @staticmethod
    def quick_push():
        print("[*] Starting Quick Push...")
        try:
            subprocess.run(["git", "add", "."], check=True, capture_output=True, text=True)
            msg = input("Commit message (e.g., 'fixed bug'): ").strip() or "auto-commit"
            subprocess.run(["git", "commit", "-m", msg], check=True, capture_output=True, text=True)
            
            # Authentication Injection for Push
            token = os.getenv("GITHUB_TOKEN") or os.getenv("GH_TOKEN")
            remote_url = subprocess.run(["git", "remote", "get-url", "origin"], capture_output=True, text=True).stdout.strip()
            
            if token and "github.com" in remote_url and "https://" in remote_url:
                auth_url = remote_url.replace("https://", f"https://{token}@")
                subprocess.run(["git", "push", auth_url, "main"], check=True, capture_output=True, text=True)
            else:
                subprocess.run(["git", "push", "origin", "main"], check=True, capture_output=True, text=True)
                
            AutomationTools.telegram_alert(f"🚀 GIT PUSH: Successfully pushed code to GitHub (Message: '{msg}')")
            print("[+] Push complete. Your code is LIVE!")
        except subprocess.CalledProcessError as e:
            AutomationTools.telegram_alert(f"❌ GIT PUSH FAILED: {e.stderr}")
            print(f"[-] Push error: {e.stderr}")

    @staticmethod
    def pull_updates():
        print("[*] Pulling latest updates from GitHub...")
        try:
            branch = input("Branch to pull (default: main): ").strip() or "main"
            subprocess.run(["git", "fetch", "origin", branch], check=True)
            subprocess.run(["git", "reset", "--hard", f"origin/{branch}"], check=True)
            AutomationTools.telegram_alert(f"📥 GIT PULL: Synced with origin/{branch}")
            print(f"[+] SUCCESS: Your folder is perfectly synced with origin/{branch}")
        except subprocess.CalledProcessError as e:
            print(f"[-] Pull error: Ensure you are inside a Git repository.")

    @staticmethod
    def clone_repo():
        print("[*] Clone an existing GitHub repository to your PC")
        
        # Clipboard Detection Feature
        clipboard_content = ""
        if pyperclip:
            try:
                clipboard_content = pyperclip.paste().strip()
            except:
                pass
        
        suggested_url = ""
        if clipboard_content.startswith("https://github.com/"):
            suggested_url = clipboard_content
            print(f"[!] Detected GitHub URL in clipboard: {suggested_url}")
            use_clipboard = input("Do you want to use this URL? (y/n, default: y): ").strip().lower()
            if use_clipboard in ["y", "yes", ""]:
                repo_url = suggested_url
            else:
                repo_url = input("Enter the GitHub repository URL: ").strip()
        else:
            repo_url = input("Enter the GitHub repository URL: ").strip()

        if repo_url:
            try:
                subprocess.run(["git", "clone", repo_url], check=True)
                AutomationTools.telegram_alert(f"🏗️ GIT CLONE: Successfully cloned {repo_url}")
                print(f"[+] Successfully cloned {repo_url}")
            except subprocess.CalledProcessError:
                print("[-] Failed to clone. Check the URL.")

class ProjectArchitect:
    @staticmethod
    def initialize():
        folder = input("New project folder name: ").strip()
        if not folder: return
        try:
            if not os.path.exists(folder):
                os.makedirs(folder)
            
            original_dir = os.getcwd()
            os.chdir(folder)
            subprocess.run(["git", "init"], check=True)
            
            with open("LICENSE", "w") as f:
                f.write("MIT License\n\nCopyright (c) 2026 Crizneil Bucio\n\nPermission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the \"Software\"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:\n\nThe above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.\n\nTHE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.")
            
            with open("README.md", "w") as f:
                f.write(f"# {folder}\n\nProject initialized by CRIZ TOOLS.")
            
            with open(".gitignore", "w") as f:
                f.write("__pycache__/\n.env\n*.log\n")
            
            print(f"[+] Project '{folder}' architected successfully. Ready to code!")
            AutomationTools.telegram_alert(f"🆕 PROJECT ARCHITECT: Initialized project '{folder}'")
            os.chdir(original_dir)
        except Exception as e:
            print(f"[-] Architect error: {e}")

class PersonalTools:
    @staticmethod
    def github_streak():
        # Check if in a git repository
        try:
            subprocess.run(["git", "rev-parse", "--is-inside-work-tree"], check=True, capture_output=True, text=True)
        except subprocess.CalledProcessError:
            print("[-] Streak Error: You are not inside a Git repository.")
            print("[!] Please move this tool to a Git repo folder or run 'git init' first.")
            return

        print("[*] Triggering Daily Streak Commit...")
        streak_dir = "streak"
        streak_file = os.path.join(streak_dir, "contribute.txt")
        
        if not os.path.exists(streak_dir):
            os.makedirs(streak_dir)
            
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        with open(streak_file, "a") as f:
            f.write(f"Streak updated at: {timestamp}\n")
            
        try:
            subprocess.run(["git", "add", streak_file], check=True, capture_output=True, text=True)
            subprocess.run(["git", "commit", "-m", f"daily streak: {timestamp}"], check=True, capture_output=True, text=True)
            
            # Authentication Injection for Push
            token = os.getenv("GITHUB_TOKEN") or os.getenv("GH_TOKEN")
            remote_url = subprocess.run(["git", "remote", "get-url", "origin"], capture_output=True, text=True).stdout.strip()
            
            if token and "github.com" in remote_url and "https://" in remote_url:
                auth_url = remote_url.replace("https://", f"https://{token}@")
                subprocess.run(["git", "push", auth_url, "main"], check=True, capture_output=True, text=True)
            else:
                subprocess.run(["git", "push", "origin", "main"], check=True, capture_output=True, text=True)
                
            print(f"[+] Streak successfully updated and pushed! Graph is GREEN. [{timestamp}]")
            AutomationTools.telegram_alert(f"🔥 GITHUB STREAK: Auto-commit successful at {timestamp}")
        except subprocess.CalledProcessError as e:
            print(f"[-] Streak Error:")
            if "could not read from remote repository" in str(e.stderr).lower():
                print("    > Error: Cannot connect to GitHub. Check your internet or SSH/HTTPS access.")
            elif "src refspec main does not match any" in str(e.stderr).lower():
                print("    > Error: Your branch might not be 'main'. Try 'master'?")
            else:
                print(f"    > {e.stderr}")
        except Exception as e:
            print(f"[-] Unexpected Streak Error: {e}")

    @staticmethod
    def get_github_client():
        token = os.getenv("GITHUB_TOKEN") or os.getenv("GH_TOKEN")
        if not token:
            print("[-] Error: GITHUB_TOKEN or GH_TOKEN not found in .env file.")
            print("[!] Please add GITHUB_TOKEN=your_token_here to your .env file.")
            return None
        auth = Auth.Token(token)
        return Github(auth=auth)

    @staticmethod
    def auto_follow():
        g = PersonalTools.get_github_client()
        if not g: return
        
        query = input("Enter search query for users to follow (e.g., 'python developer'): ").strip()
        if not query: return
        
        limit = input("Limit (default: 5): ").strip() or "5"
        try:
            limit = int(limit)
        except ValueError:
            limit = 5
            
        print(f"[*] Searching for users matching '{query}'...")
        users = g.search_users(query)
        
        count = 0
        for user in users:
            if count >= limit: break
            try:
                g.get_user().add_to_following(user)
                print(f"[+] Followed: {user.login}")
                count += 1
            except Exception as e:
                print(f"[-] Failed to follow {user.login}: {e}")
        
        print(f"[+] Done. Followed {count} users.")
        AutomationTools.telegram_alert(f"👤 AUTO-FOLLOW: Followed {count} users for query '{query}'")

    @staticmethod
    def auto_unfollow():
        g = PersonalTools.get_github_client()
        if not g: return
        
        print("[*] Fetching followers and following lists...")
        user = g.get_user()
        following = user.get_following()
        followers = [f.login for f in user.get_followers()]
        
        print(f"[*] You are following {following.totalCount} users.")
        unfollow_count = 0
        
        for f_user in following:
            if f_user.login not in followers:
                print(f"[*] {f_user.login} is not following you back. Unfollowing...")
                try:
                    user.remove_from_following(f_user)
                    print(f"[+] Unfollowed: {f_user.login}")
                    unfollow_count += 1
                except Exception as e:
                    print(f"[-] Failed to unfollow {f_user.login}: {e}")
        
        print(f"[+] Done. Unfollowed {unfollow_count} non-followers.")
        AutomationTools.telegram_alert(f"🧹 AUTO-UNFOLLOW: Removed {unfollow_count} non-followers.")

class PowerTools:
    @staticmethod
    def project_snapshot():
        print("[*] Creating Project Snapshot (Zipped Backup)...")
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        project_name = os.path.basename(os.getcwd())
        zip_name = f"snapshot_{project_name}_{timestamp}.zip"
        backup_dir = "backups"
        
        if not os.path.exists(backup_dir):
            os.makedirs(backup_dir)
            
        zip_path = os.path.join(backup_dir, zip_name)
        ignore_folders = {'.git', '.venv', '__pycache__', 'node_modules', 'backups'}
        
        try:
            with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
                for root, dirs, files in os.walk('.'):
                    dirs[:] = [d for d in dirs if d not in ignore_folders]
                    for file in files:
                        file_path = os.path.join(root, file)
                        zipf.write(file_path, os.path.relpath(file_path, '.'))
            print(f"[+] Snapshot created successfully: {zip_path}")
            AutomationTools.telegram_alert(f"📦 PROJECT SNAPSHOT: Created backup '{zip_name}'")
        except Exception as e:
            print(f"[-] Snapshot Error: {e}")

    @staticmethod
    def internet_speed_test():
        print("[*] Running Internet Speed Test (this may take a minute)...")
        try:
            import speedtest
            st = speedtest.Speedtest()
            print("[*] Finding best server...")
            st.get_best_server()
            print("[*] Testing Download speed...")
            download_speed = st.download() / 1_000_000
            print("[*] Testing Upload speed...")
            upload_speed = st.upload() / 1_000_000
            ping = st.results.ping
            print(f"[+] Download: {download_speed:.2f} Mbps")
            print(f"[+] Upload: {upload_speed:.2f} Mbps")
            print(f"[+] Ping: {ping} ms")
            AutomationTools.telegram_alert(f"🚀 INTERNET SPEED: {download_speed:.2f} Mbps Down / {upload_speed:.2f} Mbps Up")
        except ImportError:
            print("[-] speedtest-cli not installed. Please run: pip install speedtest-cli")
        except Exception as e:
            print(f"[-] Speed Test Error: {e}")

    @staticmethod
    def battery_status():
        print("[*] Checking Battery Status...")
        battery = psutil.sensors_battery()
        if battery:
            plugged = "Plugged In" if battery.power_plugged else "Running on Battery"
            percent = battery.percent
            print(f"[+] Percentage: {percent}%")
            print(f"[+] Status: {plugged}")
            if not battery.power_plugged:
                seconds = battery.secsleft
                if seconds == psutil.POWER_TIME_UNKNOWN:
                    print("[+] Time Remaining: Calculating...")
                else:
                    print(f"[+] Time Remaining: {seconds // 3600}h {(seconds % 3600) // 60}m")
            
            AutomationTools.telegram_alert(f"🔋 BATTERY STATUS: {percent}% ({plugged})")
        else:
            print("[-] No battery detected (likely a Desktop PC).")

    @staticmethod
    def quick_notes():
        notes_file = ".omni_notes.json"
        if not os.path.exists(notes_file):
            with open(notes_file, 'w') as f:
                json.dump([], f)
        
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            print("--- [bold magenta]QUICK NOTES SCRATCHPAD[/bold magenta] ---")
            with open(notes_file, 'r') as f:
                notes = json.load(f)
            
            if not notes:
                print("\n(No notes saved yet)\n")
            else:
                for idx, note in enumerate(notes, 1):
                    print(f"[{idx}] {note}")
            
            print("\n[A] Add Note | [D] Delete Note | [B] Back")
            cmd = input("NOTES@OMNI:~$ ").strip().lower()
            
            if cmd == 'a':
                new_note_text = input("Enter note: ").strip()
                if new_note_text:
                    notes.append({"id": len(notes)+1, "text": new_note_text, "time": datetime.now().strftime("%Y-%m-%d %H:%M")})
                    PowerTools.save_notes(notes)
                    print("[+] Note captured.")
                    AutomationTools.telegram_alert(f"📝 QUICK NOTES: Added new note: {new_note_text[:50]}...")
            elif cmd == 'd':
                try:
                    num = int(input("Enter note number to delete: "))
                    if 0 < num <= len(notes):
                        removed = notes.pop(num-1)
                        PowerTools.save_notes(notes)
                        print(f"[+] Removed: {removed['text']}")
                        AutomationTools.telegram_alert(f"🗑️ QUICK NOTES: Deleted note: {removed['text'][:50]}...")
                    else:
                        print("[-] Invalid note number.")
                except ValueError:
                    print("[-] Invalid input. Please enter a number.")
                except Exception as e:
                    print(f"[-] Error deleting note: {e}")
            elif cmd == 'b':
                break

    @staticmethod
    def directory_cleanup():
        print("[*] Running Ghost Directory Cleanup (removing empty folders)...")
        deleted_count = 0
        for root, dirs, files in os.walk('.', topdown=False):
            for name in dirs:
                full_path = os.path.join(root, name)
                if not os.listdir(full_path):
                    try:
                        os.rmdir(full_path)
                        print(f"[+] Deleted: {full_path}")
                        deleted_count += 1
                    except Exception as e:
                        pass
        print(f"[+] Done. Removed {deleted_count} empty directories.")
        AutomationTools.telegram_alert(f"🧹 GHOST CLEANUP: Removed {deleted_count} empty folders.")

class DevTools:
    @staticmethod
    def port_scanner():
        print("[*] Scanning common local ports for active services...")
        common_ports = [80, 443, 3000, 3306, 5000, 5432, 8000, 8080, 27017]
        active_ports = []
        
        for port in common_ports:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.settimeout(0.3)
                if s.connect_ex(('127.0.0.1', port)) == 0:
                    active_ports.append(port)
        
        if active_ports:
            print(f"[+] Active Ports found on localhost: {', '.join(map(str, active_ports))}")
            AutomationTools.telegram_alert(f"🔍 PORT SCANNER: Found active ports: {', '.join(map(str, active_ports))}")
        else:
            print("[-] No common local ports are currently open.")
            AutomationTools.telegram_alert("🔍 PORT SCANNER: No common developer ports found open.")

    @staticmethod
    def env_validator():
        print("[*] Validating .env against .env.example...")
        example_file = ".env.example"
        env_file = ".env"
        
        if not os.path.exists(example_file):
            print("[-] .env.example not found.")
            return
        
        required_keys = []
        with open(example_file, 'r') as f:
            for line in f:
                if '=' in line and not line.strip().startswith('#'):
                    required_keys.append(line.split('=')[0].strip())
        
        if not os.path.exists(env_file):
            print("[-] .env file is COMPLETELY MISSING!")
            return
            
        current_keys = {}
        with open(env_file, 'r') as f:
            for line in f:
                if '=' in line and not line.strip().startswith('#'):
                    current_keys[line.split('=')[0].strip()] = True
        
        missing = [key for key in required_keys if key not in current_keys]
        if missing:
            print(f"[-] Missing keys in .env: {', '.join(missing)}")
            AutomationTools.telegram_alert(f"⚠️ ENV VALIDATOR: Missing keys found: {', '.join(missing)}")
        else:
            print("[+] Perfect! All required keys from .env.example are present.")
            AutomationTools.telegram_alert("✅ ENV VALIDATOR: All required .env keys are present.")

    @staticmethod
    def api_tester():
        print("[*] CLI API Tester (Mini-Postman)")
        url = input("Enter API URL: ").strip()
        if not url: return
        
        method = input("Method (GET/POST, default: GET): ").strip().upper() or "GET"
        
        try:
            if method == "POST":
                data = input("Body (JSON, optional): ").strip()
                payload = json.loads(data) if data else {}
                response = requests.post(url, json=payload, timeout=10)
            else:
                response = requests.get(url, timeout=10)
            
            print(f"\n[+] Status Code: {response.status_code}")
            print(f"[+] Response Content:")
            print(response.text[:500] + ("..." if len(response.text) > 500 else ""))
            AutomationTools.telegram_alert(f"🌐 API TESTER: {method} {url} returned {response.status_code}")
        except Exception as e:
            print(f"[-] API Test Error: {e}")

    @staticmethod
    def auto_readme_tech():
        print("[*] Scanning project dependencies for README.md update...")
        techs = []
        if os.path.exists("requirements.txt"):
            with open("requirements.txt", 'r') as f:
                techs.extend([line.strip() for line in f if line.strip() and not line.startswith('#')])
        
        if techs:
            tech_str = "\n".join([f"- {t}" for t in techs])
            content = f"## 🚀 Technologies Used\n{tech_str}\n"
            
            try:
                if os.path.exists("README.md"):
                    with open("README.md", "a") as f:
                        f.write(f"\n{content}")
                    print("[+] Technology stack added to README.md")
                    AutomationTools.telegram_alert("📝 README TECH: Updated tech stack in README.md")
                else:
                    print("[-] README.md not found.")
            except Exception as e:
                print(f"[-] README Update Error: {e}")
        else:
            print("[-] No dependencies found to list.")

class WindowsTools:
    @staticmethod
    def flush_dns():
        print("[*] Flushing DNS Cache...")
        subprocess.run(["ipconfig", "/flushdns"])
        AutomationTools.telegram_alert("🌐 WINDOWS: DNS Cache Flushed.")
    
    @staticmethod
    def clean_temp():
        print("[*] Cleaning Windows Temporary Files...")
        temp_dir = os.environ.get('TEMP')
        if temp_dir:
            try:
                subprocess.run(['del', '/q/f/s', f'{temp_dir}\\*'], shell=True, stderr=subprocess.DEVNULL)
                print("[+] Temp files wiped. Disk space recovered!")
            except:
                print("[-] Failed to clear some temp files (they might be actively used by Windows).")
                
    @staticmethod
    def ping_test():
        print("[*] Pinging Google (8.8.8.8) to check internet dropouts...")
        subprocess.run(["ping", "8.8.8.8"])
        AutomationTools.telegram_alert("📡 WINDOWS: Ping test completed.")
        
    @staticmethod
    def update_apps():
        print("[*] Commands Windows to find updates for all your installed software...")
        try:
            subprocess.run(["winget", "upgrade"], check=True)
            print("\n[!] To automatically install all updates, run: winget upgrade --all")
            AutomationTools.telegram_alert("🆙 WINDOWS: Winget check for app updates finished.")
        except (subprocess.CalledProcessError, FileNotFoundError):
            print("[-] Winget not found or failed. Ensure Windows App Installer is updated.")
            AutomationTools.telegram_alert("⚠️ WINDOWS: Winget update check failed or not installed.")

class AutomationTools:
    @staticmethod
    def ai_commit_suggestion():
        print("[*] Analyzing git diff for AI commit suggestion...")
        try:
            diff = subprocess.check_output(["git", "diff", "--cached"], text=True)
            if not diff:
                diff = subprocess.check_output(["git", "diff"], text=True)
            
            if not diff:
                print("[-] No changes detected to analyze.")
                return
            
            api_key = os.getenv("GEMINI_API_KEY")
            if not api_key:
                print("[!] GEMINI_API_KEY not found. Using rule-based suggestion:")
                # Simple rule-based logic if no AI
                if "main.py" in diff: print("> feat: update main tool logic")
                elif "README" in diff: print("> docs: update documentation")
                else: print("> chore: minor updates")
                return

            print("[*] Requesting AI suggestion (Simulated)...")
            suggestion = "feat: implement advanced automation and power tools suite"
            print(f"> {suggestion}")
            AutomationTools.telegram_alert(f"🤖 AI COMMIT SUGGESTION: {suggestion}")
        except Exception as e:
            print(f"[-] AI Suggestion Error: {e}")

    @staticmethod
    def telegram_alert(message=None):
        print("[*] Sending Telegram Notification...")
        token = os.getenv("TELEGRAM_BOT_TOKEN")
        chat_ids = os.getenv("TELEGRAM_CHAT_ID")
        
        if not token or not chat_ids:
            print("[-] Error: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID missing in .env")
            return
            
        if not message:
            message = input("Enter message to send: ").strip() or "Hello from Omni-Tool!"
            
        url = f"https://api.telegram.org/bot{token}/sendMessage"
        
        # Support multiple IDs comma-separated
        for chat_id in chat_ids.split(','):
            chat_id = chat_id.strip().strip('"').strip("'")
            if not chat_id: continue
            payload = {"chat_id": chat_id, "text": message}
            
            try:
                response = requests.post(url, json=payload, timeout=10)
                if response.status_code == 200:
                    print(f"[+] Notification sent to {chat_id}!")
                else:
                    print(f"[-] Failed to send to {chat_id}: {response.text}")
            except Exception as e:
                print(f"[-] Telegram Error for {chat_id}: {e}")

    @staticmethod
    def send_photo(photo_path, caption=None):
        token = os.getenv("TELEGRAM_BOT_TOKEN")
        chat_ids = os.getenv("TELEGRAM_CHAT_ID")
        if not token or not chat_ids: return
        
        url = f"https://api.telegram.org/bot{token}/sendPhoto"
        for chat_id in chat_ids.split(','):
            chat_id = chat_id.strip().strip('"').strip("'")
            if not chat_id: continue
            try:
                with open(photo_path, 'rb') as photo:
                    files = {'photo': photo}
                    data = {'chat_id': chat_id, 'caption': caption}
                    requests.post(url, files=files, data=data, timeout=20)
            except Exception as e:
                print(f"[-] Telegram Photo Error for {chat_id}: {e}")

class TelegramBotListener:
    def __init__(self):
        self.token = os.getenv("TELEGRAM_BOT_TOKEN")
        self.chat_ids = [idx.strip().strip('"').strip("'") for idx in (os.getenv("TELEGRAM_CHAT_ID") or "").split(',') if idx.strip()]
        self.last_update_id = 0
        self.running = True

    def start(self):
        if not self.token or not self.chat_ids:
            # Silent fail for listener if no token
            return
        thread = threading.Thread(target=self.poll, daemon=True)
        thread.start()

    def poll(self):
        # Clear backlog: Get the latest update ID and skip everything before it
        try:
            url = f"https://api.telegram.org/bot{self.token}/getUpdates"
            response = requests.get(url, params={"offset": -1, "timeout": 1}, timeout=5).json()
            if response.get("ok") and response.get("result"):
                self.last_update_id = response["result"][0]["update_id"]
        except Exception:
            pass

        while self.running:
            try:
                url = f"https://api.telegram.org/bot{self.token}/getUpdates"
                params = {"offset": self.last_update_id + 1, "timeout": 30}
                response = requests.get(url, params=params, timeout=35).json()
                
                if response.get("ok"):
                    for update in response.get("result", []):
                        self.last_update_id = update["update_id"]
                        message = update.get("message")
                        if not message: continue
                        
                        chat_id = str(message["chat"]["id"])
                        username = message.get("from", {}).get("username", "Unknown")
                        text = message.get("text", "").lower()
                        
                        if chat_id not in self.chat_ids:
                            continue 
                            
                        self.handle_command(text, chat_id)
            except Exception:
                time.sleep(15)

    def handle_command(self, text, chat_id):
        if text.startswith("/status"):
            cpu = psutil.cpu_percent()
            ram = psutil.virtual_memory().percent
            disk = psutil.disk_usage('/').percent
            AutomationTools.telegram_alert(f"🖥️ PC Status:\nCPU: {cpu}%\nRAM: {ram}%\nDisk: {disk}%")
        
        elif text.startswith("/screenshot"):
            if not pyautogui:
                AutomationTools.telegram_alert("[-] Screenshot error: Missing graphical dependencies.")
                print("[!] Requested screenshot but pyautogui is not imported.")
                return
            AutomationTools.telegram_alert("[*] Capturing screen...")
            path = "temp_screenshot.png"
            try:
                pyautogui.screenshot(path)
                AutomationTools.send_photo(path, "📸 Current PC Screen")
                os.remove(path)
            except Exception as e:
                AutomationTools.telegram_alert(f"[-] Screenshot failed: {e}")
            
        elif text.startswith("/backup"):
            AutomationTools.telegram_alert("[*] Remote Snapshot requested...")
            PowerTools.project_snapshot()
            AutomationTools.telegram_alert("[+] Project Snapshot completed successfully.")
            
        elif text.startswith("/streak"):
            AutomationTools.telegram_alert("[*] Remote Streak requested...")
            PersonalTools.github_streak()
            
        elif text.startswith("/ping"):
            AutomationTools.telegram_alert("🏓 Pong! Station is Online.")
            
        elif text.startswith("/shutdown"):
            AutomationTools.telegram_alert("⚠️ SHUTDOWN COMMAND RECEIVED. PC will turn off in 1 minute.")
            os.system("shutdown /s /t 60")
            
        elif text.startswith("/restart"):
            AutomationTools.telegram_alert("🔄 RESTART COMMAND RECEIVED. PC will restart in 1 minute.")
            os.system("shutdown /r /t 60")
        
        elif text.startswith("/help"):
            help_text = "🤖 Omni-Bot Commands:\n/status - System usage\n/screenshot - Visual check\n/backup - Save project\n/streak - Maintenance\n/ping - Check life\n/shutdown - Turn off\n/restart - Reboot"
            AutomationTools.telegram_alert(help_text)

class SystemMonitor:
    def __init__(self):
        self.running = True

    def start(self):
        # Only start if Telegram is configured
        if not os.getenv("TELEGRAM_BOT_TOKEN"): return
        thread = threading.Thread(target=self.monitor, daemon=True)
        thread.start()
        print("[+] Automated System Monitor started (Background).")

    def monitor(self):
        while self.running:
            try:
                disk = psutil.disk_usage('/')
                if disk.percent > 90:
                    AutomationTools.telegram_alert(f"🚨 ALERT: LOW DISK SPACE ({disk.percent}% used)!")
                
                cpu = psutil.cpu_percent(interval=1)
                if cpu > 95:
                    AutomationTools.telegram_alert(f"🚨 ALERT: HIGH CPU USAGE ({cpu}%)!")
                
                time.sleep(300) 
            except Exception:
                time.sleep(60)


class TerminalUI:
    def __init__(self):
        self.console = Console()
        self.banner = """
[bold cyan]  ██████╗██████╗ ██╗███████╗  [/bold cyan] [bold yellow] ████████╗██████╗  ██████╗ ██╗     ███████╗[/bold yellow]
[bold cyan] ██╔════╝██╔══██╗██║╚══███╔╝  [/bold cyan] [bold yellow] ╚══██╔══╝██╔══██╗██╔═══██╗██║     ██╔════╝[/bold yellow]
[bold cyan] ██║     ██████╔╝██║  ███╔╝   [/bold cyan] [bold yellow]    ██║   ██║  ██║██║   ██║██║     ███████╗[/bold yellow]
[bold cyan] ██║     ██╔══██╗██║ ███╔╝    [/bold cyan] [bold yellow]    ██║   ██║  ██║██║   ██║██║     ╚════██║[/bold yellow]
[bold cyan] ╚██████╗██║  ██║██║███████╗  [/bold cyan] [bold yellow]    ██║   ██████╔╝╚██████╔╝███████╗███████║[/bold yellow]
[bold cyan]  ╚═════╝╚═╝  ╚═╝╚═╝╚══════╝  [/bold cyan] [bold yellow]    ╚═╝   ╚═════╝  ╚═════╝ ╚══════╝╚══════╝[/bold yellow]
        """

    def get_system_monitor(self):
        cpu = psutil.cpu_percent()
        ram = psutil.virtual_memory().percent
        disk = psutil.disk_usage('/').percent
        stats = f"CPU: {cpu}% | RAM: {ram}% | DISK: {disk}%"
        return Panel(Text(stats, justify="center", style="bold green"), style="green")

    def run(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print(self.get_system_monitor())
            
            self.console.print("\n[1] [bold magenta]PERSONAL TOOLS[/bold magenta] (Auto Streak & Social)")
            self.console.print("[2] [bold cyan]GITHUB TOOLS[/bold cyan] (Manage Code & Repositories)")
            self.console.print("[3] [bold yellow]WINDOWS TOOLS[/bold yellow] (Clean & Fix your PC)")
            self.console.print("[4] [bold blue]OMNI POWER TOOLS[/bold blue] (Snapshots, Speed, etc.)")
            self.console.print("[5] [bold green]DEVELOPER TOOLS[/bold green] (Ports, API, etc.)")
            self.console.print("[6] [bold white]AUTOMATION & AI[/bold white] (AI Commits & Alerts)")
            self.console.print("[0] EXIT\n")
            
            choice = input("CRIZ@OMNI:~$ ").strip()

            try:
                if choice == "1":
                    self.personal_menu()
                elif choice == "2":
                    self.github_menu()
                elif choice == "3":
                    self.windows_menu()
                elif choice == "4":
                    self.power_tools_menu()
                elif choice == "5":
                    self.dev_tools_menu()
                elif choice == "6":
                    self.automation_menu()
                elif choice == "0":
                    print("STATION OFFLINE.")
                    break
            except Exception as e:
                print(f"\n[bold red]CRITICAL ERROR IN MAIN LOOP:[/bold red] {e}")
                input("Press ENTER to return to menu...")

    def github_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold cyan]GITHUB TOOLS[/bold cyan] ---")
            self.console.print("[1] PROJECT ARCHITECT (Auto-creates new repo folder, README, & License)")
            self.console.print("[2] GIT QUICK PUSH (Auto-adds all files, asks for commit message, and pushes)")
            self.console.print("[3] GIT PULL UPDATES (Force-downloads the newest code from GitHub)")
            self.console.print("[4] GIT CLONE REPO (Downloads an existing GitHub project to your PC)")
            self.console.print("[0] BACK TO MAIN\n")
            
            gh_choice = input("GITHUB@OMNI:~$ ").strip()
            
            if gh_choice == "1":
                ProjectArchitect.initialize()
            elif gh_choice == "2":
                GitCenter.quick_push()
            elif gh_choice == "3":
                GitCenter.pull_updates()
            elif gh_choice == "4":
                GitCenter.clone_repo()
            elif gh_choice == "0":
                break
                
            if gh_choice in ["1", "2", "3", "4"]:
                input("\nPress ENTER to continue...")

    def windows_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold yellow]WINDOWS TOOLS[/bold yellow] ---")
            self.console.print("[1] NETWORK PING TEST (Checks your internet stability)")
            self.console.print("[2] FLUSH DNS (Fixes internet routing issues)")
            self.console.print("[3] SYSTEM CLEAN-UP (Deletes junk %temp% files to save space)")
            self.console.print("[4] WINGET APP UPDATER (Checks for software updates)")
            self.console.print("[0] BACK TO MAIN\n")
            
            win_choice = input("WINDOWS@OMNI:~$ ").strip()
            
            if win_choice == "1":
                WindowsTools.ping_test()
            elif win_choice == "2":
                WindowsTools.flush_dns()
            elif win_choice == "3":
                WindowsTools.clean_temp()
            elif win_choice == "4":
                WindowsTools.update_apps()
            elif win_choice == "0":
                break
                
            if win_choice in ["1", "2", "3", "4"]:
                input("\nPress ENTER to continue...")

    def personal_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold magenta]PERSONAL TOOLS[/bold magenta] ---")
            self.console.print("[1] AUTO GITHUB STREAK (Trigger daily commit to current repo)")
            self.console.print("[2] AUTO FOLLOW (Find and follow users based on query)")
            self.console.print("[3] AUTO UNFOLLOW (Unfollow anyone who doesn't follow you back)")
            self.console.print("[0] BACK TO MAIN\n")
            
            p_choice = input("PERSONAL@OMNI:~$ ").strip()
            
            if p_choice == "1":
                PersonalTools.github_streak()
            elif p_choice == "2":
                PersonalTools.auto_follow()
            elif p_choice == "3":
                PersonalTools.auto_unfollow()
            elif p_choice == "0":
                break
                
            if p_choice in ["1", "2", "3"]:
                input("\nPress ENTER to continue...")

    def power_tools_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold blue]OMNI POWER TOOLS[/bold blue] ---")
            self.console.print("[1] PROJECT SNAPSHOT (Auto-create ZIP backup)")
            self.console.print("[2] INTERNET SPEED TEST (Download/Upload speed)")
            self.console.print("[3] BATTERY HEALTH (Status & Time remaining)")
            self.console.print("[4] QUICK NOTES (CLI Scratchpad for thoughts)")
            self.console.print("[5] GHOST CLEANUP (Remove empty directories)")
            self.console.print("[0] BACK TO MAIN\n")
            
            choice = input("POWER@OMNI:~$ ").strip()
            if choice == "1": PowerTools.project_snapshot()
            elif choice == "2": PowerTools.internet_speed_test()
            elif choice == "3": PowerTools.battery_status()
            elif choice == "4": PowerTools.quick_notes()
            elif choice == "5": PowerTools.directory_cleanup()
            elif choice == "0": break
            if choice in ["1", "2", "3", "5"]: input("\nPress ENTER to continue...")

    def dev_tools_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold green]DEVELOPER TOOLS[/bold green] ---")
            self.console.print("[1] LOCAL PORT SCANNER (Check active dev ports)")
            self.console.print("[2] .ENV VALIDATOR (Check for missing keys)")
            self.console.print("[3] CLI API TESTER (Mini-Postman)")
            self.console.print("[4] AUTO-README TECH (Identify dependencies)")
            self.console.print("[0] BACK TO MAIN\n")
            
            choice = input("DEV@OMNI:~$ ").strip()
            if choice == "1": DevTools.port_scanner()
            elif choice == "2": DevTools.env_validator()
            elif choice == "3": DevTools.api_tester()
            elif choice == "4": DevTools.auto_readme_tech()
            elif choice == "0": break
            if choice in ["1", "2", "3", "4"]: input("\nPress ENTER to continue...")

    def automation_menu(self):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print("\n--- [bold white]AUTOMATION & AI[/bold white] ---")
            self.console.print("[1] AI COMMIT SUGGESTION (Analyze git diff)")
            self.console.print("[2] SEND TELEGRAM ALERT (Send message to phone)")
            self.console.print("[0] BACK TO MAIN\n")
            
            choice = input("AUTO@OMNI:~$ ").strip()
            if choice == "1": AutomationTools.ai_commit_suggestion()
            elif choice == "2": AutomationTools.telegram_alert()
            elif choice == "0": break
            if choice in ["1", "2"]: input("\nPress ENTER to continue...")

def main():
    # Start Background Services
    bot = TelegramBotListener()
    bot.start()
    
    monitor = SystemMonitor()
    monitor.start()

    ui = TerminalUI()
    ui.run()

if __name__ == "__main__":
    main()