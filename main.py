import os
import sys
import time
import argparse
import datetime
import psutil
import logging
import subprocess
from github import Github
from rich.console import Console
from rich.panel import Panel
from rich.table import Table
from rich.live import Live
from rich.text import Text
from rich.logging import RichHandler
from dotenv import load_dotenv, set_key

# Load environment variables
load_dotenv()

# Configure logging
logging.basicConfig(
    level="INFO",
    format="%(message)s",
    datefmt="[%X]",
    handlers=[RichHandler(rich_tracebacks=True, show_path=False)]
)
logger = logging.getLogger("rich")

class GitHubManager:
    def __init__(self, token=None):
        self.token = token or os.getenv("GH_TOKEN")
        if not self.token:
            self.token = self.auto_auth()
        
        try:
            self.gh = Github(self.token)
            self.user = self.gh.get_user()
            logger.info(f"AUTH SUCCESS: Connected as {self.user.login}")
        except Exception as e:
            logger.error(f"AUTH FAILED: {e}")
            sys.exit(1)

    def auto_auth(self):
        """Prompt for token and save to .env"""
        print("\n[!] GH_TOKEN missing in environment.")
        token = input("Enter your GitHub Personal Access Token: ").strip()
        if not token:
            print("CRITICAL: Token is required.")
            sys.exit(1)
        
        env_path = ".env"
        if not os.path.exists(env_path):
            with open(env_path, "w") as f:
                f.write("")
        
        set_key(env_path, "GH_TOKEN", token)
        print("[+] Token saved to .env")
        return token

    def pulse_heartbeat(self):
        """[1] Daily Pulse: Health check log"""
        log_dir = "logs"
        if not os.path.exists(log_dir):
            os.makedirs(log_dir)
        
        timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        log_path = os.path.join(log_dir, "pulse.log")
        
        with open(log_path, "a") as f:
            f.write(f"HEALTH_CHECK: {timestamp} - SYSTEM_OK\n")
        
        logger.info(f"PULSE: Heartbeat recorded at {timestamp}")

    def network_sync(self):
        """[2] Network Sync: Follow and Unfollow"""
        # Follow logic
        keyword = 'Laravel Philippines'
        logger.info(f"SYNC: Searching users in '{keyword}'...")
        users = self.gh.search_users(keyword)
        follow_count = 0
        for user in users:
            if follow_count >= 5: break
            try:
                if user.login != self.user.login:
                    self.user.add_to_following(user)
                    logger.info(f"FOLLOW: {user.login}")
                    follow_count += 1
                    time.sleep(1)
            except Exception:
                continue
        
        # Unfollow logic
        logger.info("AUDIT: Checking non-followers...")
        followers = set(f.login for f in self.user.get_followers())
        following = self.user.get_following()
        unfollow_count = 0
        for user in following:
            if user.login not in followers:
                try:
                    self.user.remove_from_following(user)
                    logger.info(f"UNFOLLOW: {user.login}")
                    unfollow_count += 1
                    time.sleep(1)
                except Exception:
                    continue
        
        logger.info(f"RESULT: +{follow_count} follows, -{unfollow_count} unfollows.")

class GitCenter:
    @staticmethod
    def quick_push():
        """[3] Git: Quick Push"""
        try:
            subprocess.run(["git", "add", "."], check=True)
            msg = input("Commit message: ").strip() or "update: workstation sync"
            subprocess.run(["git", "commit", "-m", msg], check=True)
            subprocess.run(["git", "push", "origin", "main"], check=True)
            print("[+] Push complete.")
        except subprocess.CalledProcessError as e:
            print(f"[-] Git error: {e}")

    @staticmethod
    def friend_sync():
        """[3] Git: Friend Sync"""
        try:
            branch = input("Branch to sync: ").strip() or "main"
            subprocess.run(["git", "fetch"], check=True)
            subprocess.run(["git", "merge", f"origin/{branch}"], check=True)
            print(f"[+] Synced with origin/{branch}")
        except subprocess.CalledProcessError as e:
            print(f"[-] Sync error: {e}")

class ProjectArchitect:
    @staticmethod
    def initialize():
        """[4] Architect: Init project"""
        folder = input("Project name: ").strip()
        if not folder: return
        
        try:
            if not os.path.exists(folder):
                os.makedirs(folder)
            
            # Save current dir to return later
            original_dir = os.getcwd()
            os.chdir(folder)
            subprocess.run(["git", "init"], check=True)
            
            # Auto-License
            with open("LICENSE", "w") as f:
                f.write(f"MIT License\n\nCopyright (c) 2026 Crizneil Bucio\n\n...") # Simplified
            
            # Scaffolding
            with open("README.md", "w") as f:
                f.write(f"# {folder}\n\nProject initialized by GH-CRIZ OMNI TOOL.")
            
            with open(".gitignore", "w") as f:
                f.write("__pycache__/\n.env\n*.log\n")
            
            print(f"[+] Project '{folder}' architected successfully.")
            os.chdir(original_dir)
        except Exception as e:
            print(f"[-] Architect error: {e}")

class TerminalUI:
    def __init__(self):
        self.console = Console()
        self.banner = """
[bold green]
  GH-CRIZ OMNI TOOL
  -----------------
  STATION: PISCES
  STATUS:  OPERATIONAL
  SYSTEM:  DEVOPS-CLI
[/bold green]
        """

    def get_system_monitor(self):
        cpu = psutil.cpu_percent()
        ram = psutil.virtual_memory().percent
        disk = psutil.disk_usage('/').percent
        
        stats = f"CPU: {cpu}% | RAM: {ram}% | DISK: {disk}%"
        return Panel(Text(stats, style="bold green"), title="[white]SYSTEM MONITOR[/white]", border_style="green")

    def run(self, manager):
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            self.console.print(self.banner)
            self.console.print(self.get_system_monitor())
            
            self.console.print("\n[1] DAILY PULSE")
            self.console.print("[2] NETWORK SYNC")
            self.console.print("[3] GIT CENTER")
            self.console.print("[4] PROJECT ARCHITECT")
            self.console.print("[0] EXIT\n")
            
            choice = input("CRIZ@OMNI:~$ ").strip()

            if choice == "1":
                manager.pulse_heartbeat()
            elif choice == "2":
                manager.network_sync()
            elif choice == "3":
                print("\n[1] QUICK PUSH\n[2] FRIEND SYNC")
                git_choice = input("GIT@OMNI:~$ ").strip()
                if git_choice == "1":
                    GitCenter.quick_push()
                elif git_choice == "2":
                    GitCenter.friend_sync()
            elif choice == "4":
                ProjectArchitect.initialize()
            elif choice == "0":
                print("STATION OFFLINE.")
                break
            
            if choice in ["1", "2", "3", "4"]:
                input("\nPress ENTER to continue...")

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--auto", action="store_true")
    args = parser.parse_args()

    manager = GitHubManager()

    if args.auto:
        manager.pulse_heartbeat()
        sys.exit(0)

    ui = TerminalUI()
    ui.run(manager)

if __name__ == "__main__":
    main()