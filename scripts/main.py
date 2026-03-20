import os
import sys
import argparse
import subprocess
import psutil
from rich.console import Console
from rich.panel import Panel
from rich.text import Text

class GitCenter:
    @staticmethod
    def quick_push():
        print("[*] Starting Quick Push...")
        try:
            subprocess.run(["git", "add", "."], check=True)
            msg = input("Commit message (e.g., 'fixed bug'): ").strip() or "auto-commit"
            subprocess.run(["git", "commit", "-m", msg], check=True)
            subprocess.run(["git", "push", "origin", "main"], check=True)
            print("[+] Push complete. Your code is LIVE!")
        except subprocess.CalledProcessError as e:
            print(f"[-] Push error: Ensure you are in a Git repository and have rights.")

    @staticmethod
    def pull_updates():
        print("[*] Pulling latest updates from GitHub...")
        try:
            branch = input("Branch to pull (default: main): ").strip() or "main"
            subprocess.run(["git", "fetch", "origin", branch], check=True)
            subprocess.run(["git", "reset", "--hard", f"origin/{branch}"], check=True)
            print(f"[+] SUCCESS: Your folder is perfectly synced with origin/{branch}")
        except subprocess.CalledProcessError as e:
            print(f"[-] Pull error: Ensure you are inside a Git repository.")

    @staticmethod
    def clone_repo():
        print("[*] Clone an existing GitHub repository to your PC")
        repo_url = input("Enter the GitHub repository URL: ").strip()
        if repo_url:
            try:
                subprocess.run(["git", "clone", repo_url], check=True)
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
            os.chdir(original_dir)
        except Exception as e:
            print(f"[-] Architect error: {e}")

class WindowsTools:
    @staticmethod
    def flush_dns():
        print("[*] Flushing DNS Cache...")
        subprocess.run(["ipconfig", "/flushdns"])
    
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
        
    @staticmethod
    def update_apps():
        print("[*] Commands Windows to find updates for all your installed software...")
        subprocess.run(["winget", "upgrade"])
        print("\n[!] To automatically install all updates, run: winget upgrade --all")

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
            
            self.console.print("\n[1] [bold cyan]GITHUB TOOLS[/bold cyan] (Manage Code & Repositories)")
            self.console.print("[2] [bold yellow]WINDOWS TOOLS[/bold yellow] (Clean & Fix your PC)")
            self.console.print("[0] EXIT\n")
            
            choice = input("CRIZ@OMNI:~$ ").strip()

            if choice == "1":
                self.github_menu()
            elif choice == "2":
                self.windows_menu()
            elif choice == "0":
                print("STATION OFFLINE.")
                break

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

def main():
    ui = TerminalUI()
    ui.run()

if __name__ == "__main__":
    main()