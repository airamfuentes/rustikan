<script setup>
defineProps({
    clase: {
        type: String,
        default: 'h-12 w-12',
    },
});
</script>

<template>
    <!-- Logo Rustikan con espiga de trigo y R -->
    <svg :class="clase" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Espiga de trigo -->
        <g class="espiga">
            <!-- Tallo central -->
            <path d="M30 20 L30 75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            
            <!-- Granos (hojas) -->
            <ellipse cx="25" cy="30" rx="4" ry="6" fill="currentColor" opacity="0.9"/>
            <ellipse cx="35" cy="33" rx="4" ry="6" fill="currentColor" opacity="0.9"/>
            <ellipse cx="24" cy="40" rx="4" ry="6" fill="currentColor" opacity="0.85"/>
            <ellipse cx="36" cy="43" rx="4" ry="6" fill="currentColor" opacity="0.85"/>
            <ellipse cx="23" cy="50" rx="4" ry="6" fill="currentColor" opacity="0.8"/>
            <ellipse cx="37" cy="53" rx="4" ry="6" fill="currentColor" opacity="0.8"/>
            <ellipse cx="24" cy="60" rx="3.5" ry="5" fill="currentColor" opacity="0.75"/>
            <ellipse cx="36" cy="63" rx="3.5" ry="5" fill="currentColor" opacity="0.75"/>
        </g>
        
        <!-- Letra R estilizada -->
        <g class="letra-r">
            <path d="M 50 35 L 50 75 M 50 35 C 50 35, 65 35, 65 47 C 65 55, 60 55, 55 55 L 50 55 M 55 55 L 70 75" 
                  stroke="currentColor" 
                  stroke-width="4" 
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  fill="none"/>
        </g>
    </svg>
</template>

<style scoped>
.espiga {
    transform-origin: 30px 50px;
}

.letra-r {
    transform-origin: 60px 55px;
}
</style>
