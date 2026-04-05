---
name: phpstan
description: >
  Run PHPStan static analysis on the project.
  Use this skill to know how to run PHPStan and how to configure it
  to run automatically after file modifications in Claude Code.
---

# Skill: PHPStan

## Running PHPStan

```bash
castor qa:phpstan
```

This command runs PHPStan (level 8) on the entire `src/` directory inside the PHP Docker container.

---

## Automatic execution via Claude Code hooks

Claude Code supports hooks — shell commands triggered on specific events. To make PHPStan run automatically after each code modification, configure a hook in `.claude/settings.json`.

### How Claude Code hooks work

| Event | Claude can react? | When it runs |
|-------|-------------------|--------------|
| `PostToolUse` (Edit/Write) | No | Immediately after each file edit |
| `Stop` | No | When Claude finishes its turn |
| `UserPromptSubmit` | **Yes** | Before Claude processes the user's next message |

**`UserPromptSubmit` is the only hook where Claude sees the output and can react to fix errors.**

### Recommended configuration: `UserPromptSubmit` with git guard

Add this to `.claude/settings.json`:

```json
{
  "hooks": {
    "UserPromptSubmit": [
      {
        "hooks": [
          {
            "type": "command",
            "command": "cd /path/to/project/application && if git diff --name-only HEAD 2>/dev/null | grep -q '\\.php$'; then castor qa:phpstan 2>&1 | tail -40; fi"
          }
        ]
      }
    ]
  }
}
```

**How it works:**
1. Claude modifies PHP files
2. User sends their next message (anything)
3. Before Claude processes it, the hook checks `git diff` for modified `.php` files
4. If PHP files were modified → PHPStan runs and the output is injected into Claude's context
5. Claude sees the errors and can fix them in that same turn

**The `git diff` guard** prevents PHPStan from running on every single message (e.g. when just asking questions).

### Alternative: `PostToolUse` (immediate, Claude cannot react)

If you just want to see PHPStan output after each file save without needing Claude to react:

```json
{
  "hooks": {
    "PostToolUse": [
      {
        "matcher": "Write|Edit",
        "hooks": [
          {
            "type": "command",
            "command": "cd /path/to/project/application && castor qa:phpstan 2>&1 | tail -30"
          }
        ]
      }
    ]
  }
}
```

Output is visible via `Ctrl+O` (transcript view) but Claude does not see it.

---

## Settings file locations

| File | Scope | Committed to repo |
|------|-------|-------------------|
| `.claude/settings.json` | Project only | Yes |
| `.claude/settings.local.json` | Project only | No (gitignored) |
| `~/.claude/settings.json` | All projects | — |

Use `.claude/settings.json` to share the hook configuration with the team.
