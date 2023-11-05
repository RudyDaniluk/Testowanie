import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

public class PlayerTest {
    private Player player;

    @BeforeEach
    public void setUp() {
        player = new Player(true, "JohnDoe", 2000);
    }

    @Test
    public void testCreatePlayer() {
        assertEquals("JohnDoe", player.getName());
        assertEquals(Integer.valueOf(2000), player.getBornYear());
        assertTrue(player.getAgree());
    }

    @Test
    public void testSetName() {
        player.setName("Alice-Smith");
        assertEquals("Alice-Smith", player.getName());
    }

    @Test
    public void testSetBornYear() {
        player.setBornYear(1995);
        assertEquals(Integer.valueOf(1995), player.getBornYear());
    }

    @Test
    public void testInvalidName() {
        assertThrows(IllegalArgumentException.class, () -> player.setName("Invalid@Name"));
    }

    @Test
    public void testUnderagePlayer() {
        assertThrows(IllegalArgumentException.class, () -> player.setBornYear(2023));
    }
}