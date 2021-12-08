#!/usr/bin/env python3

import subprocess
import tempfile
import socket
import random
import shutil
import time
import ast
import sys
import os

# This challenge relies on several resources:
# qemu-user-static to do multi-arch support
# shellcode runners compiled into different architectures
# a shuffl binary to handle sandboxing
RESOURCE_DIR = os.path.dirname(os.path.realpath(__file__))
SHUFFL_PATH = os.path.join(RESOURCE_DIR, "shuffl")

# This defines the length and composition of the secret that submitted shellcodes must read out.
SECRET_LENGTH = 32
SECRET_MIN = 0x61
SECRET_MAX = 0x7a

# These are the valid architectures for shellcode.
VALID_ARCHES = { "x86_64", "aarch64", "riscv64" }

# This timeout defines the length of time a shellcode can go "undefeated" before the game is wiped.
VICTORY_TIMEOUT = int(os.environ.get("VICTORY_TIMEOUT", "900"))

# These are the initial blocked bytes.
INITIAL_BLOCKED = set(bytes.fromhex(os.environ.get("INITIAL_BLOCKED", "90")))

# Here, we figure out what team's private pod this service is running on.
if "TEAM_NAME" in os.environ:
	TEAM_NAME = os.environ["TEAM_NAME"]
else:
	TEAMS = {1: 'DiceGang', 2: 'HITCON ⚔️ Balsn', 3: 'Katzebin', 4: 'mhackeroni', 5: 'NorseCode', 6: '春秋GAME-Nu1L', 7: 'ooorganizers', 8: 'pasten', 9: 'Plaid Parliament of Pwning', 10: 'PTB_WTL', 11: 'r3kapig', 12: 'Shellphish', 13: 'StarBugs', 14: 'Perfect ⚔️ Guesser', 15: 'Tea Deliverers', 16: '侍' }
	try:
		TEAM_NAME = os.environ["REMOTE_HOST"]
	except (IndexError, KeyError, ValueError):
		TEAM_NAME = "Zardus"

# Read in the history of successful shellcode.
try:
	history = ast.literal_eval(open("history.txt").read())
	assert all(set(a.keys()) == { 'team', 'code', 'time', 'blocked', 'winner' } for a in history)
except FileNotFoundError:
	history = [ ]

# Depending on several factors, the challenges our shellcode must overcome will vary.
if not history:
	# If the challenge just launched, we'll just need to avoid the initial blocked bytes.
	previous_king = b""
	blocked_bytes = INITIAL_BLOCKED
elif time.time() - history[-1]['time'] < VICTORY_TIMEOUT:
	# If the previous shellcode has been king for less than VICTORY_TIMEOUT, then we will need
	# to beat it.
	previous_king = history[-1]['code']
	blocked_bytes = history[-1]['blocked']
else:
	# If the previous shellcode has been king for long enough, we'll reset. To prevent
	# the previous king(s) from just coming back, we'll make sure to block at least
	# one byte of each previous winner.
	previous_king = b""
	r = random.Random(1337 + sum(1 for e in history if e['winner']))
	history[-1]['winner'] = True
	blocked_bytes = { r.choice(e['code']) for e in history if e['winner'] }

def print_status():
	print("# Current hill:")
	print("GLORIOUS VICTORY")
	for entry in reversed(history[-10:]):
		if entry['winner']:
			break
		print(
			f"... under that, code from team {entry['team']} at {int(entry['time'])}: {entry['code'].hex()}")

	print("")
	print("# Renowned ancestors")
	print("... Zardus made the battlefield")
	prior_winners = [ e for e in reversed(history) if e['winner'] ]
	for entry in prior_winners:
		print(f"... then {entry['team']} {random.choice(['stumped the world', 'honored their ancestors', 'showed their strength', 'wowed their elders', 'achieved greatness', 'defeated all challengers'])} with {entry['code'].hex()}")

	print("")
	print("# Blocked bytes: " +" ".join("%02x"%b for b in sorted(blocked_bytes)))
	print("# Allowed bytes: " + " ".join("%02x"%b for b in sorted(set(range(256)) - blocked_bytes)))

def receive_shellcode():
	print("")
	print("# Let's do this.")
	shellcode = bytes.fromhex(input(f"Input your hex-encoded shellcode: "))[:4096]
	return shellcode

def validate_bytes(shellcode, previous_shellcode):
	# make sure the shellcode contains no banned bytes
	bad_bytes = set(shellcode) & blocked_bytes
	if bad_bytes:
		print(f"Shellcode contains blocked bytes: {' '.join('%02x'%b for b in sorted(bad_bytes))}")
		return False

	# if this is the first shellcode, accept it
	if not previous_shellcode:
		print("Congrats! You might be first to get onto the hill.")
		return True

	# if the shellcode avoids bytes used by the previous winner, accept it.
	# The avoided bytes will be added to the block list for future submissions.
	if set(previous_shellcode) - set(shellcode):
		print("Congrats! You have evolved past the bytes used by your opponents.")
		return True

	# if the shellcode is shorter, accept it
	if len(shellcode) < len(previous_shellcode):
		print("Congrats! You have progressed on your journey to elegance.")
		return True

	print("Alas! To surmount the hill, your shellcode must discard the bytes of the past or embrace austerity.")
	return False

def validate_functionality(shellcode):
	# Let's set up our sandbox.
	tmpdir = tempfile.mkdtemp()
	os.chmod(tmpdir, 0o755)
	shutil.copy(SHUFFL_PATH, tmpdir)
	for arch in VALID_ARCHES:
		shutil.copy(os.path.join(RESOURCE_DIR, f"runner-{arch}"), tmpdir)
		shutil.copy(f"/usr/bin/qemu-{arch}-static", tmpdir)

	# write the secret that must be read out by the shellcode
	secret = bytes(random.randrange(SECRET_MIN, SECRET_MAX) for _ in range(SECRET_LENGTH))
	with open(os.path.join(tmpdir, "secret"), "wb") as o:
		o.write(secret)

	# We'll try the shellcode in every valid architecture...
	for arch in VALID_ARCHES:
		print(f"Trying your shellcode as {arch}....")

		# This runs shuffl, which will sandbox into the directory and run
		# the right shellcode launcher via the appropriate qemu.
		# The shellcode must print the contents of the "secret" file to
		# stdout. Output on stderr comes back to you, in case it's useful.
		# 	p = subprocess.Popen([
		# 		os.path.join(tmpdir, os.path.basename(SHUFFL_PATH)), "",
		# 		f"./qemu-{arch}-static", "-g", "1234", f"./runner-{arch}"
		# 	], cwd=tmpdir, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=1)
		# else:
		p = subprocess.Popen([
				os.path.join(
					tmpdir, os.path.basename(SHUFFL_PATH)), "0",
				f"./qemu-{arch}-static", f"./runner-{arch}"
			], cwd=tmpdir, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=1)
		output, _ = p.communicate(shellcode)
		print(f"Your shellcode returned with return code {p.returncode}.")
		if output == secret:
			print("CONGRATULATIONS! You have taken the hill!")
			shutil.rmtree(tmpdir)
			return True
		else:
			print(f"Unfortunately, your shellcode printed {output} instead of the secret ({secret}).")

	print(f"The shellcode does not seem to work in any valid architecture...")
	shutil.rmtree(tmpdir)
	return False

def main():
	print_status()
	if history and TEAM_NAME == history[-1]['team']:
		print("You already own this hill!")
		sys.exit(3)

	shellcode = receive_shellcode()
	if not validate_bytes(shellcode, previous_king):
		print("Your shellcode did not pass the constraints.")
		sys.exit(1)
	if not validate_functionality(shellcode):
		print("Your shellcode did not print the secret.")
		sys.exit(2)

	# block bytes that were avoided by the new shellcode (and should now be blocked)
	newly_blocked = set(previous_king) - set(shellcode)
	if newly_blocked:
		print(f"You are immune to the following bytes: {' '.join('%02x'%b for b in newly_blocked)}")

	new_entry = {
		'team': TEAM_NAME,
		'time': time.time(),
		'code': shellcode,
		'blocked': newly_blocked | blocked_bytes,
		'winner': False
	}
	history.append(new_entry)

	with open("history.txt", "w") as gs:
		gs.write(repr(history))

	leaderboard = [ ]
	for e in reversed(history):
		if e['team'] not in leaderboard:
			leaderboard.append(e['team'])
		if e['winner']:
			break

	with open("/score", "w") as lb:
		lb.write(str(int(time.time())))
		lb.write("\n")
		lb.write("BLOCKED BYTES: " + " ".join("%02x"%b for b in sorted(newly_blocked | blocked_bytes)))
		lb.write("\n")
		lb.write(f"LEADERBOARD:\n{leaderboard}")

main()
